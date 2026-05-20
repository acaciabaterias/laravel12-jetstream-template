import http from 'k6/http';
import { check, fail } from 'k6';

const defaultHeaders = {
    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'User-Agent': 'k6-bateriaexpert/1.0',
};

export function getBaseUrl() {
    return (__ENV.BASE_URL || 'http://127.0.0.1:8000').replace(/\/$/, '');
}

export function getCredentials() {
    return {
        email: __ENV.USER_EMAIL || 'vendedor.demo@bateriaexpert.test',
        password: __ENV.USER_PASSWORD || 'password',
    };
}

export function getTenantBaseDomain() {
    return (__ENV.TENANT_BASE_DOMAIN || 'erp.local').replace(/^\.+|\.+$/g, '');
}

export function getTenantHosts() {
    const configuredHosts = (__ENV.TENANT_HOSTS || '')
        .split(',')
        .map((value) => value.trim())
        .filter((value) => value !== '');

    if (configuredHosts.length > 0) {
        return configuredHosts;
    }

    const tenantPrefix = (__ENV.TENANT_PREFIX || 'loadtest').trim();
    const tenantCount = Number.parseInt(__ENV.TENANT_COUNT || '100', 10);
    const safeTenantCount = Number.isNaN(tenantCount) || tenantCount < 1 ? 100 : tenantCount;
    const tenantBaseDomain = getTenantBaseDomain();

    return Array.from({ length: safeTenantCount }, (_, index) => {
        const ordinal = String(index + 1).padStart(3, '0');

        return `${tenantPrefix}-${ordinal}.${tenantBaseDomain}`;
    });
}

export function pickTenantHost(iteration = 0, vuId = 1) {
    const tenantHosts = getTenantHosts();

    if (tenantHosts.length === 0) {
        fail('Nenhum tenant host disponivel para o teste de carga multi-tenant.');
    }

    return tenantHosts[(Math.max(vuId, 1) - 1 + iteration) % tenantHosts.length];
}

function headersForTenant(tenantHost = null, overrides = {}) {
    const headers = {
        ...defaultHeaders,
        ...overrides,
    };

    if (tenantHost) {
        headers.Host = tenantHost;
        headers['X-Forwarded-Host'] = tenantHost;
    }

    return headers;
}

export function getCsrfTokenFromHtml(html) {
    const match = html.match(/name="_token"\s+value="([^"]+)"/);

    return match ? match[1] : null;
}

export function htmlDecode(value) {
    return value
        .replace(/&quot;/g, '"')
        .replace(/&#039;/g, "'")
        .replace(/&amp;/g, '&')
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>');
}

export function extractLivewireComponent(html, componentName) {
    const matches = html.matchAll(/wire:id="([^"]+)"[^>]*wire:snapshot="([^"]+)"/g);

    for (const match of matches) {
        const snapshotEncoded = htmlDecode(match[2]);
        const snapshot = JSON.parse(snapshotEncoded);

        if (snapshot.memo?.name === componentName) {
            return {
                id: match[1],
                snapshot,
                snapshotEncoded,
            };
        }
    }

    return null;
}

export function extractSelectValues(html, modelName) {
    const pattern = new RegExp(`<select[^>]*wire:model\\.live="${modelName}"[^>]*>([\\s\\S]*?)<\\/select>`, 'i');
    const selectMatch = html.match(pattern);

    if (!selectMatch) {
        return [];
    }

    return Array.from(selectMatch[1].matchAll(/<option value="(\d+)"/g)).map((match) => Number(match[1]));
}

export function login(baseUrl, credentials = getCredentials(), tenantHost = null) {
    const loginPage = http.get(`${baseUrl}/login`, {
        headers: headersForTenant(tenantHost),
        redirects: 0,
        tags: { name: 'login_page' },
    });

    check(loginPage, {
        'login page returned 200': (response) => response.status === 200,
    });

    const csrfToken = getCsrfTokenFromHtml(loginPage.body);

    if (!csrfToken) {
        fail('Nao foi possivel extrair o token CSRF da tela de login.');
    }

    const response = http.post(
        `${baseUrl}/login`,
        {
            _token: csrfToken,
            email: credentials.email,
            password: credentials.password,
        },
        {
            headers: {
                ...headersForTenant(tenantHost),
                'Content-Type': 'application/x-www-form-urlencoded',
                'Referer': `${baseUrl}/login`,
            },
            redirects: 0,
            tags: { name: 'login_submit' },
        },
    );

    check(response, {
        'login redirected': (result) => result.status === 302,
    });

    return {
        csrfToken,
        jar: response.cookies,
    };
}

export function fetchDashboard(baseUrl) {
    return fetchTenantDashboard(baseUrl, null);
}

export function fetchTenantDashboard(baseUrl, tenantHost = null) {
    const response = http.get(`${baseUrl}/dashboard`, {
        headers: headersForTenant(tenantHost),
        redirects: 0,
        tags: { name: 'dashboard_page' },
    });

    check(response, {
        'dashboard returned 200': (result) => result.status === 200,
    });

    return response;
}

export function livewireCall(baseUrl, csrfToken, component, updates, calls, tagName) {
    return livewireTenantCall(baseUrl, csrfToken, component, updates, calls, tagName, null);
}

export function livewireTenantCall(baseUrl, csrfToken, component, updates, calls, tagName, tenantHost = null) {
    const response = http.post(
        `${baseUrl}/livewire/update`,
        JSON.stringify({
            _token: csrfToken,
            components: [
                {
                    snapshot: component.snapshotEncoded,
                    updates,
                    calls,
                },
            ],
        }),
        {
            headers: {
                ...headersForTenant(tenantHost),
                'Content-Type': 'application/json',
                'X-Livewire': '1',
                'Accept': 'application/json, text/plain, */*',
                'Referer': `${baseUrl}/dashboard`,
            },
            tags: { name: tagName },
        },
    );

    check(response, {
        [`${tagName} returned 200`]: (result) => result.status === 200,
    });

    const payload = response.json();
    const snapshotEncoded = payload?.components?.[0]?.snapshot;

    if (!snapshotEncoded) {
        fail(`A resposta do Livewire nao trouxe snapshot atualizado para ${tagName}.`);
    }

    return {
        response,
        component: {
            id: component.id,
            snapshotEncoded,
            snapshot: JSON.parse(snapshotEncoded),
        },
        payload,
    };
}

export function prepareValeForm(baseUrl, csrfToken) {
    const dashboard = fetchDashboard(baseUrl);
    const valeForm = extractLivewireComponent(dashboard.body, 'vale-form');

    if (!valeForm) {
        fail('O componente Livewire vale-form nao foi encontrado no dashboard. Verifique se o usuario tem acesso-vendas.');
    }

    const clienteIds = extractSelectValues(dashboard.body, 'clienteId');
    const bateriaIds = extractSelectValues(dashboard.body, 'bateriaId');

    if (clienteIds.length === 0 || bateriaIds.length === 0) {
        fail('Nao foi possivel extrair clientes ou baterias do formulario de vale.');
    }

    return {
        csrfToken,
        valeForm,
        clienteIds,
        bateriaIds,
    };
}

export function pickByVu(values, vuId, offset = 0) {
    return values[(vuId + offset) % values.length];
}

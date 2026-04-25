import http from 'k6/http';
import { check, fail } from 'k6';
import exec from 'k6/execution';
import { getBaseUrl, getCredentials, livewireCall, login, prepareValeForm } from './_helpers.js';

export const options = {
    vus: 1,
    iterations: 1,
    thresholds: {
        http_req_failed: ['rate<0.01'],
        http_req_duration: ['p(95)<1500'],
    },
};

export default function () {
    const baseUrl = getBaseUrl();
    const credentials = getCredentials();

    const home = http.get(`${baseUrl}/`, {
        tags: { name: 'smoke_home' },
    });

    check(home, {
        'smoke home returned 200': (response) => response.status === 200,
    });

    login(baseUrl, credentials);

    const shouldCreateVale = (__ENV.SMOKE_CREATE_VALE || 'false') === 'true';

    if (!shouldCreateVale) {
        return;
    }

    const { csrfToken, valeForm, clienteIds } = prepareValeForm(baseUrl);
    const clienteId = clienteIds[exec.vu.idInTest % clienteIds.length];

    const result = livewireCall(
        baseUrl,
        csrfToken,
        valeForm,
        {
            clienteId,
            observacoes: 'Smoke test K6',
        },
        [
            {
                method: 'createVale',
                params: [],
            },
        ],
        'smoke_create_vale',
    );

    const errors = result.component.snapshot.memo?.errors || {};

    if (Object.keys(errors).length > 0) {
        fail(`Smoke createVale retornou erros de validacao: ${JSON.stringify(errors)}`);
    }
}

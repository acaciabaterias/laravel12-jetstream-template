import http from 'k6/http';
import { check, sleep } from 'k6';
import exec from 'k6/execution';
import { Counter, Rate } from 'k6/metrics';
import {
    getBaseUrl,
    pickTenantHost,
} from './_helpers.js';

const tenantProbeSuccess = new Rate('tenant_probe_success');
const tenantProbeHits = new Counter('tenant_probe_hits_total');

function envInteger(name, fallback) {
    const parsed = Number.parseInt(__ENV[name] || '', 10);

    return Number.isNaN(parsed) || parsed < 0 ? fallback : parsed;
}

function envDuration(name, fallback) {
    const value = (__ENV[name] || '').trim();

    return value === '' ? fallback : value;
}

const rampUpTarget = envInteger('LOAD_RAMP_UP_TARGET', 10);
const peakTarget = envInteger('LOAD_PEAK_TARGET', 40);
const rampUpDuration = envDuration('LOAD_RAMP_UP_DURATION', '30s');
const peakDuration = envDuration('LOAD_PEAK_DURATION', '2m');
const rampDownDuration = envDuration('LOAD_RAMP_DOWN_DURATION', '30s');

export const options = {
    scenarios: {
        multi_tenant_dashboard: {
            executor: 'ramping-vus',
            startVUs: 1,
            stages: [
                { duration: rampUpDuration, target: rampUpTarget },
                { duration: peakDuration, target: peakTarget },
                { duration: rampDownDuration, target: 0 },
            ],
            gracefulRampDown: '10s',
        },
    },
    thresholds: {
        http_req_failed: ['rate<0.05'],
        http_req_duration: ['p(95)<1200'],
        tenant_probe_success: ['rate>0.98'],
    },
};

export default function () {
    const baseUrl = getBaseUrl();
    const tenantHost = pickTenantHost(exec.scenario.iterationInTest, exec.vu.idInTest);
    const response = http.get(`${baseUrl}/load/tenant-probe`, {
        headers: {
            Host: tenantHost,
            'X-Forwarded-Host': tenantHost,
            Accept: 'application/json',
            'User-Agent': 'k6-bateriaexpert/1.0',
        },
        redirects: 0,
        tags: { name: 'tenant_probe' },
    });
    const payload = response.json();
    const ok = response.status === 200 && payload?.subdominio !== null;

    tenantProbeSuccess.add(ok, { tenant_host: tenantHost });
    tenantProbeHits.add(1, { tenant_host: tenantHost });

    check(response, {
        'tenant probe returned 200': (result) => result.status === 200,
        'tenant probe resolved a customer': () => payload?.cliente_id !== null,
        'tenant probe resolved expected subdomain': () => payload?.subdominio === tenantHost.split('.')[0],
    });

    sleep(1);
}

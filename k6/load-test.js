import http from 'k6/http';
import { check } from 'k6';

export const options = {
  discardResponseBodies: true,
  scenarios: {
    // 1. Cenário Base — health e webhook
    base_health: {
      executor: 'constant-arrival-rate',
      exec: 'health',
      rate: 200,
      timeUnit: '1s',
      duration: '1m',
      preAllocatedVUs: 50,
      maxVUs: 400,
    },
    base_webhook: {
      executor: 'constant-arrival-rate',
      exec: 'webhook',
      rate: 50,
      timeUnit: '1s',
      duration: '1m',
      preAllocatedVUs: 20,
      maxVUs: 200,
    },
    // 2. Cenário Multi-tenant (overhead do middleware)
    multi_tenant: {
      executor: 'constant-arrival-rate',
      exec: 'tenant_request',
      rate: 50,
      timeUnit: '1s',
      duration: '1m',
      preAllocatedVUs: 20,
      maxVUs: 200,
      startTime: '1m',
    },
    // 3. Cenário de Pico: ramp-up 0→1000 req/s em 30s, pico 2min, ramp-down
    peak_load: {
      executor: 'ramping-arrival-rate',
      exec: 'health',
      startRate: 0,
      timeUnit: '1s',
      preAllocatedVUs: 50,
      maxVUs: 500,
      startTime: '2m',
      stages: [
        { target: 300, duration: '30s' },
        { target: 300, duration: '2m' },
        { target: 0,   duration: '30s' },
      ],
    },
  },
  thresholds: {
    // Apenas toleramos no máximo 1% de erros globais
    'http_req_failed': ['rate<0.01'],
    // p95 < 50ms para a rota base de health
    'http_req_duration{scenario:base_health}': ['p(95)<50'],
  },
};

const BASE_URL = __ENV.BASE_URL || 'http://127.0.0.1:8500';
const INTERNAL_KEY = __ENV.INTERNAL_KEY || 'your-very-long-secret-key-here-1234567890abcdef';

export function health() {
  const res = http.get(`${BASE_URL}/api/health`);
  check(res, {
    'is status 200': (r) => r.status === 200,
  });
}

export function webhook() {
  const payload = JSON.stringify({ status: 'ok' });
  const params = {
    headers: {
      'Content-Type': 'application/json',
      'X-Internal-Service-Key': INTERNAL_KEY,
    },
  };
  const res = http.post(`${BASE_URL}/api/internal/webhooks/fiscal/status`, payload, params);
  check(res, {
    'is status 200': (r) => r.status === 200,
  });
}

export function tenant_request() {
  const tenantId = Math.floor(Math.random() * 100) + 1;
  const params = {
    headers: {
      'Host': `test${tenantId}.local`,
    },
  };
  
  // Usamos a mesma rota health para medir o overhead do middleware puro
  const res = http.get(`${BASE_URL}/api/health`, params);
  check(res, {
    'is status 200': (r) => r.status === 200,
  });
}

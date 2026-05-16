import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate } from 'k6/metrics';
import { getBaseUrl, getCredentials, fetchDashboard, login } from './_helpers.js';

const authenticatedDashboardSuccess = new Rate('authenticated_dashboard_success');

export const options = {
    scenarios: {
        concurrent_users: {
            executor: 'constant-vus',
            vus: 100,
            duration: '2m',
            gracefulStop: '15s',
        },
    },
    thresholds: {
        http_req_failed: ['rate<0.05'],
        http_req_duration: ['p(95)<2000'],
        authenticated_dashboard_success: ['rate>0.95'],
    },
};

export default function () {
    const baseUrl = getBaseUrl();
    const credentials = getCredentials();

    const home = http.get(`${baseUrl}/`, {
        tags: { name: 'home_page' },
    });

    check(home, {
        'home returned 200': (response) => response.status === 200,
    });

    login(baseUrl, credentials);

    const dashboard = fetchDashboard(baseUrl);
    const ok = dashboard.status === 200 && dashboard.body.includes('Dashboard');

    authenticatedDashboardSuccess.add(ok);

    check(dashboard, {
        'dashboard contains page header': (response) => response.body.includes('Dashboard'),
    });

    sleep(1);
}

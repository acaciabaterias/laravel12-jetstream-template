import { check, sleep } from 'k6';
import exec from 'k6/execution';
import { Counter, Rate, Trend } from 'k6/metrics';
import {
    getBaseUrl,
    getCredentials,
    livewireCall,
    login,
    pickByVu,
    prepareValeForm,
} from './_helpers.js';

const createValeSuccess = new Rate('create_vale_success');
const addItemSuccess = new Rate('add_item_success');
const valeEndToEndDuration = new Trend('vale_end_to_end_duration', true);
const valesCreated = new Counter('vales_created_total');

export const options = {
    scenarios: {
        create_vales: {
            executor: 'ramping-vus',
            startVUs: 1,
            stages: [
                { duration: '30s', target: 5 },
                { duration: '1m', target: 15 },
                { duration: '30s', target: 0 },
            ],
            gracefulRampDown: '10s',
        },
    },
    thresholds: {
        http_req_failed: ['rate<0.05'],
        http_req_duration: ['p(95)<2500'],
        create_vale_success: ['rate>0.95'],
        add_item_success: ['rate>0.95'],
        vale_end_to_end_duration: ['p(95)<5000'],
    },
};

export default function () {
    const startedAt = Date.now();
    const baseUrl = getBaseUrl();
    const credentials = getCredentials();

    login(baseUrl, credentials);

    const { csrfToken, valeForm, clienteIds, bateriaIds } = prepareValeForm(baseUrl);
    const clienteId = pickByVu(clienteIds, exec.vu.idInTest, exec.scenario.iterationInTest);
    const bateriaId = pickByVu(bateriaIds, exec.vu.idInTest, exec.scenario.iterationInTest + 1);
    const observacoes = `K6 load vale vu=${exec.vu.idInTest} iter=${exec.scenario.iterationInTest}`;

    const createVale = livewireCall(
        baseUrl,
        csrfToken,
        valeForm,
        {
            clienteId,
            observacoes,
        },
        [
            {
                method: 'createVale',
                params: [],
            },
        ],
        'livewire_create_vale',
    );

    const createErrors = createVale.component.snapshot.memo?.errors || {};
    const createOk = Object.keys(createErrors).length === 0;

    createValeSuccess.add(createOk);

    check(createVale.response, {
        'createVale completed without validation errors': () => createOk,
    });

    const addItem = livewireCall(
        baseUrl,
        csrfToken,
        createVale.component,
        {
            bateriaId,
            quantidade: 1,
            devolveuSucata: true,
            observacaoItem: `Item gerado por K6 na iteracao ${exec.scenario.iterationInTest}`,
        },
        [
            {
                method: 'addItem',
                params: [],
            },
        ],
        'livewire_add_item',
    );

    const addItemErrors = addItem.component.snapshot.memo?.errors || {};
    const addItemOk = Object.keys(addItemErrors).length === 0;

    addItemSuccess.add(addItemOk);

    check(addItem.response, {
        'addItem completed without validation errors': () => addItemOk,
    });

    if (createOk && addItemOk) {
        valesCreated.add(1);
    }

    valeEndToEndDuration.add(Date.now() - startedAt);
    sleep(1);
}

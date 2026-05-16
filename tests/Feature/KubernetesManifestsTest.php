<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class KubernetesManifestsTest extends TestCase
{
    public function test_kubernetes_production_manifest_files_exist(): void
    {
        $files = [
            'infra/kubernetes/namespace.yaml',
            'infra/kubernetes/production/deployment.yaml',
            'infra/kubernetes/production/deployment-ms-001.yaml',
            'infra/kubernetes/production/deployment-ms-002.yaml',
            'infra/kubernetes/production/deployment-ms-003.yaml',
            'infra/kubernetes/production/deployment-ms-004.yaml',
            'infra/kubernetes/production/deployment-ms-005.yaml',
            'infra/kubernetes/production/service.yaml',
            'infra/kubernetes/production/ingress.yaml',
            'infra/kubernetes/production/configmap.yaml',
            'infra/kubernetes/production/hpa.yaml',
            'infra/kubernetes/production/secret.example.yaml',
            'infra/kubernetes/production/networkpolicy.yaml',
            'infra/kubernetes/production/postgres-statefulset.yaml',
            'infra/kubernetes/production/redis-deployment.yaml',
            'infra/kubernetes/production/poddisruptionbudget.yaml',
            'infra/kubernetes/production/servicemonitor.yaml',
            'infra/kubernetes/production/sealedsecret.example.yaml',
            'infra/kubernetes/production/kustomization.yaml',
            'infra/kubernetes/production/DEPLOY_CHECKLIST.md',
            'infra/kubernetes/production/verify-k8s.sh',
            'infra/kubernetes/production/smoke-test.sh',
            '.github/workflows/deploy-k8s.yml',
            'infra/kubernetes/production/cert-manager-issuer.yaml',
            'infra/kubernetes/production/backup-cronjob.yaml',
            'infra/kubernetes/production/OPS_GUIDE.md',
        ];

        foreach ($files as $file) {
            $this->assertFileExists(base_path($file));
        }
    }

    public function test_erp_manifest_contains_web_queue_and_scheduler_deployments(): void
    {
        $manifest = file_get_contents(base_path('infra/kubernetes/production/deployment.yaml'));

        $this->assertIsString($manifest);
        $this->assertStringContainsString('name: erp-core-web', $manifest);
        $this->assertStringContainsString('name: erp-core-queue', $manifest);
        $this->assertStringContainsString('name: erp-core-scheduler', $manifest);
        $this->assertStringContainsString('kind: Deployment', $manifest);
    }

    public function test_supporting_manifests_include_service_ingress_configmap_and_hpa_resources(): void
    {
        $service = file_get_contents(base_path('infra/kubernetes/production/service.yaml'));
        $ingress = file_get_contents(base_path('infra/kubernetes/production/ingress.yaml'));
        $configMap = file_get_contents(base_path('infra/kubernetes/production/configmap.yaml'));
        $hpa = file_get_contents(base_path('infra/kubernetes/production/hpa.yaml'));
        $secret = file_get_contents(base_path('infra/kubernetes/production/secret.example.yaml'));
        $networkPolicy = file_get_contents(base_path('infra/kubernetes/production/networkpolicy.yaml'));
        $postgres = file_get_contents(base_path('infra/kubernetes/production/postgres-statefulset.yaml'));
        $redis = file_get_contents(base_path('infra/kubernetes/production/redis-deployment.yaml'));
        $pdb = file_get_contents(base_path('infra/kubernetes/production/poddisruptionbudget.yaml'));
        $serviceMonitor = file_get_contents(base_path('infra/kubernetes/production/servicemonitor.yaml'));
        $sealedSecret = file_get_contents(base_path('infra/kubernetes/production/sealedsecret.example.yaml'));
        $kustomization = file_get_contents(base_path('infra/kubernetes/production/kustomization.yaml'));
        $checklist = file_get_contents(base_path('infra/kubernetes/production/DEPLOY_CHECKLIST.md'));
        $verifyScript = file_get_contents(base_path('infra/kubernetes/production/verify-k8s.sh'));
        $smokeTest = file_get_contents(base_path('infra/kubernetes/production/smoke-test.sh'));
        $deployWorkflow = file_get_contents(base_path('.github/workflows/deploy-k8s.yml'));
        $issuer = file_get_contents(base_path('infra/kubernetes/production/cert-manager-issuer.yaml'));
        $backupCronjob = file_get_contents(base_path('infra/kubernetes/production/backup-cronjob.yaml'));
        $opsGuide = file_get_contents(base_path('infra/kubernetes/production/OPS_GUIDE.md'));
        $namespaces = file_get_contents(base_path('infra/kubernetes/namespace.yaml'));

        $this->assertIsString($service);
        $this->assertIsString($ingress);
        $this->assertIsString($configMap);
        $this->assertIsString($hpa);
        $this->assertIsString($secret);
        $this->assertIsString($networkPolicy);
        $this->assertIsString($postgres);
        $this->assertIsString($redis);
        $this->assertIsString($pdb);
        $this->assertIsString($serviceMonitor);
        $this->assertIsString($sealedSecret);
        $this->assertIsString($kustomization);
        $this->assertIsString($checklist);
        $this->assertIsString($verifyScript);
        $this->assertIsString($smokeTest);
        $this->assertIsString($deployWorkflow);
        $this->assertIsString($issuer);
        $this->assertIsString($backupCronjob);
        $this->assertIsString($opsGuide);
        $this->assertIsString($namespaces);

        $this->assertStringContainsString('kind: Service', $service);
        $this->assertStringContainsString('kind: Ingress', $ingress);
        $this->assertStringContainsString('kind: ConfigMap', $configMap);
        $this->assertStringContainsString('kind: HorizontalPodAutoscaler', $hpa);
        $this->assertStringContainsString('erp-core-web', $service);
        $this->assertStringContainsString('erp.example.com', $ingress);
        $this->assertStringContainsString('erp-core-config', $configMap);
        $this->assertStringContainsString('ms-005-geocoding-api', $hpa);
        $this->assertStringContainsString('kind: Secret', $secret);
        $this->assertStringContainsString('erp-core-secrets', $secret);
        $this->assertStringContainsString('kind: NetworkPolicy', $networkPolicy);
        $this->assertStringContainsString('default-deny-all', $networkPolicy);
        $this->assertStringContainsString('kind: StatefulSet', $postgres);
        $this->assertStringContainsString('postgres-central', $postgres);
        $this->assertStringContainsString('kind: Deployment', $redis);
        $this->assertStringContainsString('redis-master', $redis);
        $this->assertStringContainsString('kind: PodDisruptionBudget', $pdb);
        $this->assertStringContainsString('erp-core-web', $pdb);
        $this->assertStringContainsString('kind: ServiceMonitor', $serviceMonitor);
        $this->assertStringContainsString('release: kube-prometheus-stack', $serviceMonitor);
        $this->assertStringContainsString('kind: SealedSecret', $sealedSecret);
        $this->assertStringContainsString('AgReplaceWithKubesealEncryptedValue', $sealedSecret);
        $this->assertStringContainsString('kind: Kustomization', $kustomization);
        $this->assertStringContainsString('deployment-ms-005.yaml', $kustomization);
        $this->assertStringContainsString('backup-cronjob.yaml', $kustomization);
        $this->assertStringNotContainsString('secret.example.yaml', $kustomization);
        $this->assertStringNotContainsString('sealedsecret.example.yaml', $kustomization);
        $this->assertStringContainsString('## Ordem de Subida', $checklist);
        $this->assertStringContainsString('kubectl apply -k infra/kubernetes/production', $checklist);
        $this->assertStringContainsString('check_rollout deployment/erp-core-web', $verifyScript);
        $this->assertStringContainsString('K8S_NAMESPACE', $verifyScript);
        $this->assertStringContainsString('MS001_URL', $smokeTest);
        $this->assertStringContainsString('ERP Core', $smokeTest);
        $this->assertStringContainsString('name: Deploy Kubernetes', $deployWorkflow);
        $this->assertStringContainsString('kubectl apply -k infra/kubernetes/production', $deployWorkflow);
        $this->assertStringContainsString('kind: ClusterIssuer', $issuer);
        $this->assertStringContainsString('letsencrypt-prod', $issuer);
        $this->assertStringContainsString('kind: CronJob', $backupCronjob);
        $this->assertStringContainsString('postgres-central-backup', $backupCronjob);
        $this->assertStringContainsString('# OPS Guide', $opsGuide);
        $this->assertStringContainsString('kubectl logs deployment/erp-core-web', $opsGuide);
        $this->assertStringContainsString('bateriaexpert-dev', $namespaces);
        $this->assertStringContainsString('bateriaexpert-staging', $namespaces);
        $this->assertStringContainsString('bateriaexpert', $namespaces);
    }
}

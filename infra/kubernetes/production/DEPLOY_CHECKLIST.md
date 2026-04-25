# Kubernetes Deploy Checklist

## Pre-Deploy

1. Confirmar que o cluster possui `Ingress Controller`, `metrics-server`, `cert-manager` e, se aplicavel, `Prometheus Operator` e `Sealed Secrets Controller`.
2. Ajustar dominios reais em `ingress.yaml`.
3. Ajustar imagens reais no `kustomization.yaml`.
4. Revisar `configmap.yaml` com hosts reais de banco, Redis e URLs dos microservicos.
5. Gerar os `Secret` reais a partir de `secret.example.yaml` ou `SealedSecret` reais a partir de `sealedsecret.example.yaml`. Esses exemplos nao entram no `kustomization.yaml` de producao para evitar aplicar placeholders.

## Ordem de Subida

1. Criar namespaces:
   `kubectl apply -f infra/kubernetes/namespace.yaml`
2. Aplicar secrets ou sealed secrets:
   `kubectl apply -f infra/kubernetes/production/sealedsecret.example.yaml`
   Ou:
   `kubectl apply -f infra/kubernetes/production/secret.example.yaml`
3. Aplicar configuracoes base:
   `kubectl apply -f infra/kubernetes/production/configmap.yaml`
4. Subir dependencias centrais:
   `kubectl apply -f infra/kubernetes/production/postgres-statefulset.yaml`
   `kubectl apply -f infra/kubernetes/production/redis-deployment.yaml`
5. Aguardar PostgreSQL e Redis ficarem prontos:
   `kubectl rollout status statefulset/postgres-central -n bateriaexpert`
   `kubectl rollout status deployment/redis-master -n bateriaexpert`
6. Subir aplicacao ERP e microservicos:
   `kubectl apply -f infra/kubernetes/production/deployment.yaml`
   `kubectl apply -f infra/kubernetes/production/deployment-ms-001.yaml`
   `kubectl apply -f infra/kubernetes/production/deployment-ms-002.yaml`
   `kubectl apply -f infra/kubernetes/production/deployment-ms-003.yaml`
   `kubectl apply -f infra/kubernetes/production/deployment-ms-004.yaml`
   `kubectl apply -f infra/kubernetes/production/deployment-ms-005.yaml`
7. Subir services e ingress:
   `kubectl apply -f infra/kubernetes/production/service.yaml`
   `kubectl apply -f infra/kubernetes/production/ingress.yaml`
8. Aplicar politicas operacionais:
   `kubectl apply -f infra/kubernetes/production/networkpolicy.yaml`
   `kubectl apply -f infra/kubernetes/production/hpa.yaml`
   `kubectl apply -f infra/kubernetes/production/poddisruptionbudget.yaml`
   `kubectl apply -f infra/kubernetes/production/servicemonitor.yaml`

## Fluxo com Kustomize

1. Revisar o build:
   `kubectl kustomize infra/kubernetes/production`
2. Aplicar todo o pacote:
   `kubectl apply -k infra/kubernetes/production`
3. Validar rollout:
   `./infra/kubernetes/production/verify-k8s.sh`

## Validacoes Finais

1. Conferir pods:
   `kubectl get pods -n bateriaexpert`
2. Conferir services e ingress:
   `kubectl get svc,ingress -n bateriaexpert`
3. Conferir HPAs e PDBs:
   `kubectl get hpa,pdb -n bateriaexpert`
4. Testar healthchecks:
   `kubectl port-forward svc/erp-core-web -n bateriaexpert 8080:80`
   `curl -i http://127.0.0.1:8080/up`
5. Se houver Prometheus Operator, validar ServiceMonitors:
   `kubectl get servicemonitor -n bateriaexpert`

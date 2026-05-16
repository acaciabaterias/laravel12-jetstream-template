<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BancoProvider;
use App\Models\Consentimento;
use App\Services\EncryptionService;
use App\Services\Providers\MockProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OAuthController extends Controller
{
    public function __construct(
        protected EncryptionService $encryption
    ) {}

    public function authorizeProvider(string $banco): JsonResponse
    {
        $provider = BancoProvider::query()
            ->where('provider', $banco)
            ->orWhere('codigo_banco', $banco)
            ->orWhere('nome', $banco)
            ->first();

        $state = (string) Str::uuid();
        $mockProvider = new MockProvider;

        return response()->json([
            'provider' => $provider?->provider ?? $banco,
            'state' => $state,
            'authorization_url' => $mockProvider->getAuthUrl($state),
        ]);
    }

    public function callback(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'code' => ['required', 'string'],
            'provider_id' => ['nullable', 'exists:banco_providers,id'],
            'empresa_id' => ['nullable', 'integer'],
        ]);

        $provider = $payload['provider_id'] ?? BancoProvider::query()->value('id');
        $providerModel = BancoProvider::query()->findOrFail($provider);
        $tokenData = (new MockProvider)->exchangeToken($payload['code']);

        $consentimento = Consentimento::query()->create([
            'empresa_id' => $payload['empresa_id'] ?? 1,
            'provider_id' => $providerModel->id,
            'banco_nome' => $providerModel->nome,
            'banco_codigo' => $providerModel->codigo_banco,
            'status' => 'ativo',
            'access_token_encrypted' => $this->encryption->encrypt($tokenData['access_token']),
            'refresh_token_encrypted' => $this->encryption->encrypt($tokenData['refresh_token']),
            'escopo' => 'accounts transactions',
            'expira_em' => now()->addSeconds($tokenData['expires_in']),
        ]);

        return response()->json([
            'id' => $consentimento->id,
            'status' => $consentimento->status,
        ], 201);
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Consentimento::query()->with('provider')->latest('id')->get(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $consentimento = Consentimento::query()->findOrFail($id);

        if ($consentimento->access_token_encrypted) {
            (new MockProvider)->revoke($this->encryption->decrypt($consentimento->access_token_encrypted));
        }

        $consentimento->update([
            'status' => 'revogado',
        ]);

        return response()->json([
            'status' => 'revogado',
            'id' => $consentimento->id,
        ]);
    }

    public function health(): JsonResponse
    {
        return response()->json([
            'service' => 'ms-004-openfinance',
            'status' => 'ok',
            'provider' => 'mock',
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Filial;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class FilialIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_models_with_has_filial_trait_are_filtered_by_session_filial_id(): void
    {
        // Given we have two filiais
        $filial1 = Filial::factory()->create(['nome' => 'Filial 1']);
        $filial2 = Filial::factory()->create(['nome' => 'Filial 2']);

        // And each has a post
        Post::factory()->create(['title' => 'Post Filial 1', 'filial_id' => $filial1->id]);
        Post::factory()->create(['title' => 'Post Filial 2', 'filial_id' => $filial2->id]);

        // When we set the session to Filial 1
        Session::put('filial_id', $filial1->id);

        // Then we should only see the post from Filial 1
        $posts = Post::all();
        $this->assertCount(1, $posts);
        $this->assertEquals('Post Filial 1', $posts->first()->title);

        // When we switch session to Filial 2
        Session::put('filial_id', $filial2->id);

        // Then we should only see the post from Filial 2
        $posts = Post::all();
        $this->assertCount(1, $posts);
        $this->assertEquals('Post Filial 2', $posts->first()->title);
    }

    public function test_models_automatically_assign_filial_id_from_session_on_creation(): void
    {
        // Given we have a filial in session
        $filial = Filial::factory()->create();
        Session::put('filial_id', $filial->id);

        // When we create a post without explicit filial_id
        $post = Post::create(['title' => 'New Post']);

        // Then it should have the filial_id from session
        $this->assertEquals($filial->id, $post->filial_id);
    }

    public function test_global_scope_can_be_bypassed_if_needed(): void
    {
        // Given we have two filiais and their posts
        $filial1 = Filial::factory()->create();
        $filial2 = Filial::factory()->create();
        Post::factory()->create(['filial_id' => $filial1->id]);
        Post::factory()->create(['filial_id' => $filial2->id]);

        // Set session to filial 1
        Session::put('filial_id', $filial1->id);

        // When we query without global scopes
        $posts = Post::withoutGlobalScopes()->get();

        // Then we should see everything
        $this->assertCount(2, $posts);
    }

    public function test_tenant_with_subdomain_is_resolved_correctly(): void
    {
        $filial = Filial::factory()->create([
            'subdominio' => 'joao',
            'status_assinatura' => 'active',
        ]);
        
        $user = \App\Models\User::factory()->withPersonalTeam()->create(['filial_id' => $filial->id]);

        $this->actingAs($user)
            ->get('http://joao.erp.com/dashboard')
            ->assertOk()
            ->assertSee($filial->nome);
    }

    public function test_expired_subscription_blocks_access(): void
    {
        $filial = Filial::factory()->create([
            'subdominio' => 'expirada',
            'status_assinatura' => 'expired',
        ]);
        
        $user = \App\Models\User::factory()->withPersonalTeam()->create(['filial_id' => $filial->id]);

        $this->actingAs($user)
            ->get('http://expirada.erp.com/dashboard')
            ->assertStatus(402);
    }

    public function test_white_label_customizes_colors_and_logo(): void
    {
        $filial = Filial::factory()->create([
            'subdominio' => 'custom',
            'has_white_label' => true,
            'status_assinatura' => 'active',
        ]);
        
        \App\Models\WhiteLabelConfig::factory()->create([
            'filial_id' => $filial->id,
            'cor_primaria' => '#ff0000',
            'logo_url' => 'https://custom.com/logo.png',
        ]);
        
        $user = \App\Models\User::factory()->withPersonalTeam()->create(['filial_id' => $filial->id]);

        $response = $this->actingAs($user)
            ->get('http://custom.erp.com/dashboard');
            
        $response->assertSee('#ff0000');
        $response->assertSee('https://custom.com/logo.png');
    }
}

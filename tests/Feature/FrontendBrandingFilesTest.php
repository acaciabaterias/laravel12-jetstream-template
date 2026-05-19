<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FrontendBrandingFilesTest extends TestCase
{
    #[Test]
    public function it_registers_the_brand_palette_in_tailwind_configuration(): void
    {
        $contents = file_get_contents(base_path('tailwind.config.js'));

        $this->assertNotFalse($contents);
        $this->assertStringContainsString('brand:', $contents);
        $this->assertStringContainsString('accent:', $contents);
        $this->assertStringContainsString("'Poppins'", $contents);
        $this->assertStringContainsString("'Inter'", $contents);
    }

    #[Test]
    public function it_applies_white_label_variables_and_brand_shell_to_the_main_layout(): void
    {
        $contents = file_get_contents(resource_path('views/layouts/app.blade.php'));

        $this->assertNotFalse($contents);
        $this->assertStringContainsString('--brand-primary', $contents);
        $this->assertStringContainsString('BateriaExpert ERP', $contents);
        $this->assertStringContainsString('Painel Operacional', $contents);
    }

    #[Test]
    public function it_customizes_login_and_dashboard_views_with_brand_elements(): void
    {
        $login = file_get_contents(resource_path('views/auth/login.blade.php'));
        $dashboard = file_get_contents(resource_path('views/dashboard.blade.php'));
        $adminDashboard = file_get_contents(resource_path('views/admin/dashboard.blade.php'));
        $welcome = file_get_contents(resource_path('views/welcome.blade.php'));
        $adminLogin = file_get_contents(resource_path('views/admin/auth/login.blade.php'));
        $navigation = file_get_contents(resource_path('views/navigation-menu.blade.php'));
        $tenantWelcome = file_get_contents(resource_path('views/components/welcome.blade.php'));
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertNotFalse($login);
        $this->assertNotFalse($dashboard);
        $this->assertNotFalse($adminDashboard);
        $this->assertNotFalse($welcome);
        $this->assertNotFalse($adminLogin);
        $this->assertNotFalse($navigation);
        $this->assertNotFalse($tenantWelcome);
        $this->assertNotFalse($css);

        $this->assertStringContainsString('ERP especializado em baterias automotivas', $login);
        $this->assertStringContainsString('Ir para o login administrativo', $login);
        $this->assertStringContainsString('Vendas do Dia', $dashboard);
        $this->assertStringContainsString('Tenants recentes', $adminDashboard);
        $this->assertStringContainsString('Escolha sua entrada', $welcome);
        $this->assertStringContainsString("__('Go to ERP login')", $adminLogin);
        $this->assertStringContainsString('Painel do Tenant', $navigation);
        $this->assertStringContainsString('Próximas ações recomendadas', $tenantWelcome);
        $this->assertStringContainsString('family=Inter', $css);
        $this->assertStringContainsString('--brand-secondary', $css);
    }
}

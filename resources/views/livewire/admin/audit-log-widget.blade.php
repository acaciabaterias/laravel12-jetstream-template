<div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-800 dark:border-gray-700">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Logs de Auditoria Recentes</h3>
        <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-primary-600 hover:underline dark:text-primary-500">Ver todos</a>
    </div>

    {{ $this->table }}
</div>

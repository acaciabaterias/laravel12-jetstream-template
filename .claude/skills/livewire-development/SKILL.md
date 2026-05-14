---
name: livewire-development
description: "Develops reactive Livewire 4 components. Activates when creating, updating, or modifying Livewire components; working with wire:model, wire:click, wire:loading, or any wire: directives; adding real-time updates, loading states, or reactivity; debugging component behavior; writing Livewire tests; or when the user mentions Livewire, component, counter, or reactive UI."
license: MIT
metadata:
  author: laravel
---

# Livewire Development

## When to Apply

Activate this skill when:

- Creating new Livewire components
- Modifying existing component state or behavior
- Debugging reactivity or lifecycle issues
- Writing Livewire component tests
- Adding Alpine.js interactivity to components
- Working with wire: directives

## Documentation

Use `search-docs` for detailed Livewire 4 patterns and documentation.

## Basic Usage

### Creating Components

Use the `php artisan make:livewire [Posts\CreatePost]` Artisan command to create new components.

### Fundamental Concepts

- State should live on the server, with the UI reflecting it.
- All Livewire requests hit the Laravel backend; they're like regular HTTP requests. Always validate form data and run authorization checks in Livewire actions.

## Livewire 4 specifics

### Conventions (modern Livewire stack)

This application targets Livewire 4. The conventions below match the modern Livewire 3+ stack; verify sibling components and `search-docs` when in doubt.

- Use `wire:model.live` for real-time updates; `wire:model` is deferred by default.
- Components use the `App\Livewire` namespace (not `App\Http\Livewire`).
- Use `$this->dispatch()` to dispatch events (not `emit` or `dispatchBrowserEvent`).
- Prefer the layout view path used elsewhere in this app (often `components.layouts.app` rather than legacy `layouts.app`).

### New Directives

- `wire:show`, `wire:transition`, `wire:cloak`, `wire:offline`, `wire:target` are available for use.

### Alpine Integration

- Alpine ships with Livewire; don't manually duplicate Alpine.js.
- Plugins included with Alpine: persist, intersect, collapse, and focus.

## Best Practices

### Component Structure

- Livewire components require a single root element.
- Use `wire:loading` and `wire:dirty` for delightful loading states.

### Using Keys in Loops

<!-- Wire Key in Loops -->
```blade
@foreach ($items as $item)
    <div wire:key="item-{{ $item->id }}">
        {{ $item->name }}
    </div>
@endforeach
```

### Lifecycle Hooks

Prefer lifecycle hooks like `mount()`, `updatedFoo()` for initialization and reactive side effects:

<!-- Lifecycle Hook Examples -->
```php
public function mount(User $user) { $this->user = $user; }
public function updatedSearch() { $this->resetPage(); }
```

## JavaScript Hooks

You can listen for `livewire:init` to hook into Livewire initialization:

<!-- Livewire Init Hook Example -->
```js
document.addEventListener('livewire:init', function () {
    Livewire.hook('request', ({ fail }) => {
        if (fail && fail.status === 419) {
            alert('Your session expired');
        }
    });

    Livewire.hook('message.failed', (message, component) => {
        console.error(message);
    });
});
```

## Testing

<!-- Example Livewire Component Test -->
```php
Livewire::test(Counter::class)
    ->assertSet('count', 0)
    ->call('increment')
    ->assertSet('count', 1)
    ->assertSee(1)
    ->assertStatus(200);
```

<!-- Testing Livewire Component Exists on Page -->
```php
$this->get('/posts/create')
    ->assertSeeLivewire(CreatePost::class);
```

## Common Pitfalls

- Forgetting `wire:key` in loops causes unexpected behavior when items change
- Using `wire:model` expecting real-time updates (use `wire:model.live` instead on modern Livewire)
- Not validating/authorizing in Livewire actions (treat them like HTTP requests)
- Including Alpine.js separately when it's already bundled with Livewire 4

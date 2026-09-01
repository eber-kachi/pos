# AGENTS.md - Reglas del proyecto

## Regla: Tailwind CSS CDN en ProductCatalogue

**SIEMPRE** que trabajes en el módulo `Modules/ProductCatalogue`, aplica estas reglas:

1. **Usar Tailwind CSS v3 CDN** (`https://cdn.tailwindcss.com`) en TODOS los templates del módulo.
2. **NO usar Bootstrap** ni AdminLTE en los templates públicos del catálogo. Reemplazar todas las clases de Bootstrap por clases utilitarias de Tailwind.
3. **NO usar estilos inline** (`style=""`). Usar clases de Tailwind en su lugar.
4. Los archivos `.css` del módulo (style.css) deben minimizarse o eliminarse, migrando sus estilos a clases de Tailwind.
5. Para los templates de administración (template_admin/*) que extienden `layouts.app`, mantener la herencia pero convertir el contenido a Tailwind donde sea posible sin romper el layout AdminLTE padre.

### Layout del catálogo público

El layout autónomo está en `Modules/ProductCatalogue/Resources/views/layouts/catalog.blade.php`. Este layout:
- Carga Tailwind CSS v3 CDN con colores configurables desde `$config`
- Carga Font Awesome 6 CDN
- Carga Google Fonts (Inter, Poppins, Rajdhani, Roboto, Montserrat, etc.)
- Carga `vendor.js` y `functions.js` de la app (para jQuery y `__currency_convert_recursively`)
- Incluye un polyfill para `$.fn.modal('show'/'hide')` y `data-dismiss="modal"` sin Bootstrap
- Incluye CSRF token meta tag

**Todos los templates públicos del catálogo deben extender este layout:**
```blade
@extends('productcatalogue::layouts.catalog')
```

### CDN a usar
```html
<script src="https://cdn.tailwindcss.com"></script>
```

Opcionalmente con configuración:
```html
<script src="https://cdn.tailwindcss.com">
tailwind.config = {
  theme: {
    extend: {
      colors: {
        primary: '{{ $config["primary_color"] ?? "#3c8dbc" }}',
        secondary: '{{ $config["secondary_color"] ?? "#2c3e50" }}',
        accent: '{{ $config["accent_color"] ?? "#f39c12" }}',
      }
    }
  }
}
</script>
```

### Convenciones de conversión Bootstrap → Tailwind

| Bootstrap | Tailwind |
|-----------|----------|
| `.container` | `max-w-7xl mx-auto px-4` |
| `.row` | `flex flex-wrap -mx-2` |
| `.col-md-4` | `w-full md:w-1/3 px-2` |
| `.col-md-3` | `w-full md:w-1/4 px-2` |
| `.btn .btn-primary` | `bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded` |
| `.btn .btn-success` | `bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded` |
| `.btn .btn-danger` | `bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded` |
| `.btn .btn-default` | `bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded` |
| `.btn-sm` | `text-sm py-1 px-2` |
| `.btn-xs` | `text-xs py-0.5 px-2` |
| `.modal` | Tailwind modal con `fixed inset-0 z-50` |
| `.table` | `w-full text-sm text-left` |
| `.label` | `inline-block px-2 py-0.5 text-xs font-semibold rounded` |
| `.img-responsive` | `max-w-full h-auto` |
| `.pull-left` | `float-left` |
| `.pull-right` | `float-right` |
| `.text-center` | `text-center` |
| `.text-right` | `text-right` |
| `.hidden` | `hidden` |
| `.clearfix` | `clear-both` |
| `.box` | `bg-white shadow rounded-lg p-4` |
| `.well` | `bg-gray-100 rounded p-4` |

# Handoff para Claude Code — Rediseño UI "Talento RH"

> **Cómo usarlo:** copia esta carpeta (`talento-rh-handoff/`) a la raíz de tu proyecto Laravel y dile a Claude Code:
>
> `Lee talento-rh-handoff/HANDOFF.md e implementa el rediseño completo siguiendo las fases. Usa los archivos de design-reference/ como referencia visual exacta.`

---

## 0. Contexto e instrucciones para Claude Code

Proyecto: app de Recursos Humanos en **Laravel 12 (starter kit)** con sidebar "Platform / Recursos Humanos". Hay que rediseñar 5 pantallas con modo claro y oscuro, usando el sistema de diseño **Talento RH** (tokens abajo).

Antes de escribir código:

1. **Detecta el stack del frontend**: React + Inertia (shadcn/ui), Vue + Inertia o Livewire (Flux). Revisa `package.json`, `resources/js/`, `resources/views/` y `routes/web.php`. Adapta los componentes a ese stack. **No cambies de stack ni instales un framework nuevo.**
2. **Reutiliza lo que ya existe**: layout de la app, sidebar, el mecanismo de apariencia (claro/oscuro/sistema) del starter kit, las rutas de auth (Fortify o Breeze) y los modelos/controladores de Empleados, Puestos y Departamentos. Solo cambia la presentación y agrega lo que falte.
3. **No rompas las pruebas existentes**. Corre `php artisan test` y el build (`npm run build`) al final de cada fase.
4. Los archivos `design-reference/*.dc.html` son maquetas en un formato de diseño propio: **no los copies tal cual al proyecto**. Úsalos como referencia de estructura, textos, espaciados y estados. Los estilos inline `var(--token)` corresponden a las clases de Tailwind de la sección 2.
5. Los datos de las maquetas (48 colaboradores, nombres, fechas) son de ejemplo. En la app, **sácalos de la base de datos**. Si falta un dato (p. ej. contratos por vencer), muestra un estado vacío en vez de inventarlo.

---

## 1. Principios del sistema (resumen)

- Un solo color de marca (**teal**); fondos neutros (**slate**). El ámbar (`accent`) solo va de relleno en avatares y reconocimientos, nunca en texto.
- El estado siempre lleva palabra: todo badge tiene texto ("Activo", "Pendiente").
- Español neutro, tuteo, sentence case: "Nuevo empleado", no "Nuevo Empleado". Botones con verbo: "Guardar borrador", "Crear expediente".
- Fechas cortas: `12 mar 2021`.
- Número de empleado, RFC, CURP y NSS en `font-mono`. Cifras de indicadores con `tabular-nums`.
- Sin emoji. Iconos: **Heroicons outline** (24 px en navegación, 18–20 px en botones), en `currentColor`.
- Accesibilidad: `<label>` visible en cada campo, errores enlazados con `aria-describedby` + `aria-invalid`, `<th scope="col">`, anillo de foco de 2 px con offset de 2 px, `aria-label` en los botones que solo tienen icono.

---

## 2. Tokens (Tailwind v4)

Agrégalos a `resources/css/app.css`. Si el starter kit ya define variables de shadcn (`--background`, `--primary`…), **no las borres**: agrega estas junto a ellas y mapea las de shadcn a estas cuando convenga, por ejemplo `--primary: var(--brand)`.

```css
:root {
  --surface: #f8fafc;        /* fondo de la app          slate-50  */
  --surface-raised: #ffffff; /* tarjetas, tablas, modales */
  --surface-sunken: #f1f5f9; /* thead, hover, deshabilitado */
  --line: #e2e8f0;           /* divisores */
  --line-strong: #64748b;    /* borde de controles (≥3:1) */
  --ink: #0f172a;
  --ink-muted: #475569;
  --brand: #0f766e;          /* teal-700 */
  --brand-hover: #115e59;
  --brand-soft: #f0fdfa;
  --on-brand: #ffffff;
  --accent: #f59e0b;         /* solo relleno */
  --on-accent: #451a03;
  --success: #047857; --success-soft: #ecfdf5;
  --warning: #b45309; --warning-soft: #fffbeb;
  --danger:  #be123c; --danger-soft:  #fff1f2;
  --info:    #0369a1; --info-soft:    #f0f9ff;
  --focus: #0f766e;
}
.dark {
  --surface: #020617;
  --surface-raised: #0f172a;
  --surface-sunken: #1e293b;
  --line: #334155;
  --line-strong: #64748b;
  --ink: #f1f5f9;
  --ink-muted: #94a3b8;
  --brand: #2dd4bf;
  --brand-hover: #5eead4;
  --brand-soft: #042f2e;
  --on-brand: #042f2e;
  --accent: #fbbf24;
  --on-accent: #451a03;
  --success: #34d399; --success-soft: #022c22;
  --warning: #fbbf24; --warning-soft: #451a03;
  --danger:  #fb7185; --danger-soft:  #4c0519;
  --info:    #38bdf8; --info-soft:    #082f49;
  --focus: #5eead4;
  color-scheme: dark;
}

@theme inline {
  --color-surface: var(--surface);
  --color-surface-raised: var(--surface-raised);
  --color-surface-sunken: var(--surface-sunken);
  --color-line: var(--line);
  --color-line-strong: var(--line-strong);
  --color-ink: var(--ink);
  --color-ink-muted: var(--ink-muted);
  --color-brand: var(--brand);
  --color-brand-hover: var(--brand-hover);
  --color-brand-soft: var(--brand-soft);
  --color-on-brand: var(--on-brand);
  --color-accent: var(--accent);
  --color-on-accent: var(--on-accent);
  --color-success: var(--success);
  --color-success-soft: var(--success-soft);
  --color-warning: var(--warning);
  --color-warning-soft: var(--warning-soft);
  --color-danger: var(--danger);
  --color-danger-soft: var(--danger-soft);
  --color-info: var(--info);
  --color-info-soft: var(--info-soft);
  --color-focus: var(--focus);
}
```

> Si el proyecto usa `accent` de shadcn con otro significado, renombra el nuestro a `warm` (`bg-warm`, `text-on-warm`) para evitar el choque.

Tipografía: la fuente sans del proyecto (el starter kit trae Instrument Sans, se puede quedar) y `font-mono` para identificadores.

| Estilo | Clases |
|---|---|
| h1 (una por vista) | `text-3xl font-semibold tracking-tight` |
| h2 sección | `text-2xl font-semibold` |
| h3 tarjeta | `text-lg font-semibold` |
| body | `text-base` |
| tablas/forms | `text-sm` |
| label | `text-sm font-medium` |
| caption / th | `text-xs font-medium uppercase tracking-wide text-ink-muted` |
| cifra KPI | `text-3xl font-semibold tabular-nums` |
| código | `font-mono text-sm` |

Radios: botones e inputs `rounded-md`; tarjetas y tablas `rounded-xl`; StatTile y menús `rounded-lg`; avatares y badges `rounded-full`.
Sombras: tarjetas `shadow-sm border border-line`; menús `shadow-md`; modales `shadow-lg`.
Espaciado: tarjetas `p-6`, celdas `px-4 py-3`, secciones `gap-8`, contenido de la página `p-8`.

---

## 3. Componentes base (crear primero)

Crea estos componentes en la carpeta de componentes del stack (`resources/js/components/rh/*` o `resources/views/components/rh/*`).

| Componente | Especificación |
|---|---|
| **Button** | Variantes: `primary` (`bg-brand text-on-brand hover:bg-brand-hover`), `secondary` (`bg-surface-raised text-ink border border-line-strong hover:bg-surface-sunken`), `ghost` (`text-ink-muted hover:bg-surface-sunken hover:text-ink`). Altura `h-10` (`h-11` en auth), `px-4`, `rounded-md`, `text-sm font-medium`, `gap-2` con icono. Si navega, se renderiza como `<a>`/`Link`. `disabled:opacity-50 disabled:cursor-not-allowed`. |
| **TextField** | Label + input + ayuda + error. Input: `h-10 w-full rounded-md border border-line-strong bg-surface-raised px-3 text-sm text-ink placeholder:text-ink-muted focus:border-brand focus-visible:outline-2 outline-offset-2 outline-focus`. Opciones: icono a la izquierda (`pl-10`), sufijo/prefijo (`$` … `MXN`), `mono` (agrega `font-mono uppercase`), `required` (asterisco `text-danger` con `aria-hidden` y la leyenda "* Campo obligatorio" en el formulario). Con error: `border-danger`, `aria-invalid="true"` y un mensaje con icono `ExclamationCircle` en `text-xs font-medium text-danger`. |
| **Select** | Igual que el input; primera opción: "Selecciona una opción". |
| **PasswordField** | TextField con un botón de ojo (`aria-label` "Mostrar contraseña"/"Ocultar contraseña") que cambia `type`. |
| **PasswordStrength** | 4 segmentos `h-1 rounded-full`. Se suma 1 punto por cada regla: ≥8 caracteres, un número, un símbolo, ≥12 caracteres con una mayúscula. 0–1 = Débil (`danger`), 2–3 = Aceptable/Buena (`warning`), 4 = Segura (`success`). Texto: "Seguridad: X · Usa 8 caracteres o más, con números y símbolos." |
| **Badge** | `inline-flex h-6 items-center gap-1.5 rounded-full px-2.5 text-xs font-medium bg-{tone}-soft text-{tone}` con un punto de 6 px en `bg-current`. Tonos: Activo/Aprobada → success, Pendiente/Periodo de prueba/por vencer → warning, Baja/Rechazada/urgente → danger, Vacaciones/Permiso → info. Centraliza el mapeo estatus→tono en un solo lugar (un enum de PHP o un helper). |
| **Avatar** | Iniciales en un círculo de 36 px, `bg-accent text-on-accent text-[13px] font-semibold`, `aria-hidden`. Si hay foto, se muestra la foto. |
| **Card** | `bg-surface-raised border border-line rounded-xl shadow-sm`. |
| **StatTile** | Card `rounded-lg p-5`: label `text-sm font-medium text-ink-muted`, icono en una caja de 36 px `bg-brand-soft text-brand rounded-lg`, cifra KPI y meta `text-sm text-ink-muted`. |
| **DataTable** | Card con `overflow-hidden`; `thead` en `bg-surface-sunken` con th en estilo caption; filas `hover:bg-surface-sunken border-b border-line`; celdas `px-4 py-3 text-sm`. Tiene slots para toolbar, estado vacío y paginación. |
| **SegmentedFilter** | Contenedor `bg-surface-sunken p-1 rounded-lg`; cada botón `h-8 px-3 rounded-md text-sm font-medium` lleva un contador en una pastilla (`bg-surface rounded-full text-xs tabular-nums`). El activo: `bg-surface-raised text-ink shadow-sm` con `aria-pressed="true"`. |
| **Stepper** (vertical) | Lista `<ol>`; cada paso es un `<button>` con un círculo de 32 px: completado `bg-brand text-on-brand` con check, actual `bg-brand-soft text-brand border-2 border-brand` y `aria-current="step"`, pendiente `border-2 border-line-strong text-ink-muted`. Título `text-sm font-semibold` y descripción `text-xs text-ink-muted`. Un conector de 2 px entre pasos (`bg-brand` si el paso ya se completó, si no `bg-line`). |
| **ThemeToggle** | Botón de 40 px `border border-line rounded-md`, con icono sol en modo oscuro y luna en modo claro, y `aria-label` "Cambiar a modo claro/oscuro". **Debe usar el mecanismo de apariencia que ya trae el starter kit** (clase `.dark` en `<html>`, persistencia en cookie/localStorage y opción "sistema"). |
| **EmptyState** | Icono en un círculo de 48 px `bg-surface-sunken`, título `font-semibold`, texto `text-sm text-ink-muted` y un botón secundario opcional. |

---

## 4. Layout de la app (sidebar + topbar)

Referencia: `design-reference/dashboard.dc.html`.

- **Sidebar** de 272 px, `bg-surface-raised border-r border-line px-4 py-5`:
  - Logo: una caja de 36 px `bg-brand text-on-brand rounded-lg` con "TR" y el texto "Talento RH" / "Recursos Humanos" (sustituye el logo de Laravel).
  - Grupos con encabezado en estilo caption: **General** → Dashboard. **Recursos Humanos** → Empleados, Puestos, Departamentos, Solicitudes de vacante, Contratos, Comisiones, Nómina, Config. empresa (mismas rutas actuales).
  - Ítem: `h-10 px-3 rounded-lg gap-3 text-sm font-medium text-ink-muted hover:bg-surface-sunken hover:text-ink`. Activo: `bg-brand-soft text-brand` con `aria-current="page"`. Se considera activo con `request()->routeIs('empleados.*')` o su equivalente.
  - Se quitan los enlaces "Repository" y "Documentation".
  - Abajo: tarjeta de usuario (`border border-line rounded-lg p-3`) con avatar, nombre, rol y un botón de cerrar sesión con icono. Conserva el menú de usuario existente si ya tiene "Settings".
- **Topbar** de 64 px, `bg-surface-raised border-b border-line px-8`: breadcrumbs a la izquierda (`/` en `text-line-strong`, el último en `text-ink font-medium` con `aria-current`). A la derecha: una búsqueda global de 280 px (por ahora solo visual si no existe el endpoint), el botón de notificaciones con punto `bg-brand` y el ThemeToggle.
- **Contenido**: `bg-surface p-8 flex flex-col gap-8`. Encabezado de la página: h1 + subtítulo `text-base text-ink-muted` a la izquierda y las acciones a la derecha.
- Responsive: por debajo de `lg`, el sidebar pasa a un drawer (el starter kit ya lo trae con `SidebarTrigger` o `flux:sidebar.toggle`).

---

## 5. Pantallas

### 5.1 Login — `design-reference/login.dc.html`
- Split: a la izquierda una columna de 640 px (`bg-surface`, `px-12 py-8`) con logo + ThemeToggle arriba, el formulario centrado de 400 px y un pie con "© 2026 Talento RH · Aviso de privacidad · Ayuda". A la derecha un panel `bg-brand-soft border-l border-line p-16`, oculto por debajo de `lg`.
- Formulario: h1 "Inicia sesión", subtítulo "Accede a los expedientes, contratos y nómina de tu empresa.", correo (icono de sobre), contraseña (icono de candado + ojo) con el enlace "¿Olvidaste tu contraseña?" alineado con su label, checkbox "Mantener la sesión iniciada", botón primario de ancho completo "Iniciar sesión" (`h-11`) y la línea "¿Aún no tienes cuenta? **Crea una cuenta**".
- Panel: caption "RECURSOS HUMANOS" en `text-brand`, título de 36 px bold "El expediente de tu gente, en un solo lugar.", descripción, una mini-tabla decorativa (`aria-hidden`) con 3 empleados y sus badges, y una lista 2×2 de módulos con iconos.
- Conserva la lógica de Fortify/Breeze: errores de validación, `status` de sesión y rate limiting.

### 5.2 Registro — `design-reference/register.dc.html`
- El mismo split. Formulario en **2 pasos del lado del cliente**, con un indicador de 2 barras (`h-1`) etiquetadas "1. Tu cuenta" y "2. Tu empresa".
  - Paso 1: Nombre completo, Correo de trabajo, Contraseña + PasswordStrength, Confirmar contraseña y el botón "Continuar →". Antes de avanzar, valida en el cliente los campos del paso.
  - Paso 2: Nombre de la empresa, RFC de la empresa (mono, 12 caracteres, con la ayuda "12 caracteres para persona moral."), Número de colaboradores (1 a 10 / 11 a 50 / 51 a 200 / Más de 200), el checkbox obligatorio de términos y aviso de privacidad, y los botones "← Atrás" (secundario) y "Crear cuenta" (primario).
- Se envía en **un solo POST**. Si el servidor devuelve errores del paso 1, regresa automáticamente a ese paso.
- Si no existe el modelo de empresa: crea una migración `companies` (name, rfc, size) relacionada con el usuario, o guárdalo en la tabla de la Config. empresa si ya existe. Valida el RFC con una regex de persona moral: `^[A-ZÑ&]{3}\d{6}[A-Z\d]{3}$`.

### 5.3 Dashboard — `design-reference/dashboard.dc.html`
- Encabezado: "Hola, {nombre}" y "Resumen de Recursos Humanos · {fecha larga en español}" (`Carbon::now()->locale('es')->isoFormat('dddd D MMM YYYY')`). Acciones: "Solicitar vacante" (secundario) y "Nuevo empleado" (primario).
- Fila de 4 StatTiles (`grid-cols-4 gap-6`, 2 columnas en `md`):
  1. Colaboradores activos + altas del mes.
  2. Vacantes abiertas + solicitudes por aprobar.
  3. Contratos por vencer en 30 días.
  4. Próxima nómina (fecha + periodo + número de personas).
- Fila 2 (`grid-cols-[1.6fr_1fr] gap-6`):
  - **Solicitudes de vacante**: DataTable con Puesto, Departamento, Solicitó, Fecha, Estatus y un botón "Revisar" solo si está pendiente. Enlace "Ver todas".
  - **Contratos por vencer**: lista con avatar, nombre, "{tipo} · vence {fecha}" y un badge con "En N días" (danger si faltan ≤10 días, warning si ≤25, info en otro caso).
- Fila 3: **Plantilla por departamento** (barras horizontales: nombre de 160 px, track `bg-surface-sunken h-2.5 rounded-full`, relleno `bg-brand` proporcional al máximo, número `tabular-nums`) y **Cumpleaños y aniversarios** (ítems `bg-surface-sunken rounded-lg p-3` con un icono de regalo sobre `bg-accent`).
- Calcula todo en un `DashboardController` (o un componente Livewire) con consultas agregadas. Cada tarjeta sin datos muestra su EmptyState.

### 5.4 Empleados (índice) — `design-reference/empleados-index.dc.html`
- Encabezado: "Empleados" / "Expedientes, puestos y estatus laboral." Acciones: "Exportar" (secundario, con icono de descarga; exporta a CSV con los filtros actuales) y "Nuevo empleado".
- La tabla va dentro de una Card:
  - **Toolbar**: SegmentedFilter por estatus (Todos · Activo · Periodo de prueba · Vacaciones · Baja, con conteos reales) a la izquierda. A la derecha, una búsqueda de 320 px con lupa ("Buscar por nombre, número o RFC") y un select de departamento de 220 px.
  - **Columnas**: checkbox (con "Seleccionar todos" en el th), Número (mono, con el RFC debajo en `text-xs text-ink-muted`), Colaborador (avatar + nombre como enlace al expediente + correo en `text-xs`), Puesto, Departamento (muted), Ingreso (`d MMM YYYY`), Estatus (Badge) y un botón de acciones con icono `EllipsisHorizontal` y `aria-label` "Más acciones para {nombre}" (Ver, Editar, Dar de baja).
  - **Estado vacío**: "Sin resultados" / "Ningún colaborador coincide con la búsqueda o los filtros." + "Limpiar filtros".
  - **Pie**: "Mostrando **N** de T colaboradores" + paginación (anterior, páginas y siguiente, con la página actual en `bg-brand-soft text-brand`).
- Los filtros viven **en el query string** (`?q=&status=&department=`), del lado del servidor, con paginación de 15 registros y `withQueryString()`. La búsqueda lleva un debounce de 300 ms (Inertia `router.get` con `preserveState`, o `wire:model.live.debounce.300ms`).

### 5.5 Nuevo empleado (formulario por pasos) — `design-reference/empleados-create.dc.html`
- Encabezado: "Nuevo empleado" / "Datos personales y laborales del expediente." + "Cancelar" (secundario, vuelve al índice). Breadcrumb: Recursos Humanos / Empleados / Nuevo empleado.
- Layout: Stepper vertical de 300 px en una Card a la izquierda y el formulario en otra Card a la derecha.
- Cabecera del formulario: "PASO N DE 5" en caption `text-brand`, la leyenda "* Campo obligatorio", una barra de progreso (`role="progressbar"`, `h-1.5`, relleno `bg-brand` con transición) y el título h2 con la ayuda del paso.
- Campos en `grid-cols-2 gap-x-6 gap-y-5`:
  1. **Datos personales**: Nombre(s)*, Apellido paterno*, Apellido materno, Fecha de nacimiento*, Estado civil (Soltero(a), Casado(a), Unión libre, Divorciado(a), Viudo(a)), Teléfono ("A 10 dígitos, sin lada internacional."), Correo personal a 2 columnas ("Aquí llegarán sus recibos de nómina.").
  2. **Identificación**: un aviso `bg-info-soft text-info` ("Captura los datos tal como aparecen en la Constancia de Situación Fiscal y en el documento del IMSS."), RFC* (mono, 13), CURP* (mono, 18), NSS (mono, 11) y Régimen fiscal (Sueldos y salarios / Asimilados a salarios).
  3. **Domicilio**: Calle y número* a 2 columnas, Colonia*, Código postal* (5), Municipio o alcaldía y Estado*.
  4. **Datos laborales**: Puesto*, Departamento* (catálogos reales), Fecha de ingreso*, Tipo de contrato* (Tiempo indeterminado, Tiempo determinado, Periodo de prueba, Capacitación inicial), Salario diario* (con `$` y `MXN`, ayuda "Base para el cálculo de nómina y cuotas IMSS."), Jornada (Diurna/Nocturna/Mixta) y Jefe directo a 2 columnas.
  5. **Revisión**: una tarjeta por sección (`border border-line rounded-lg p-4`) con un `<dl>` en 2 columnas (140 px la etiqueta), el botón "Editar" (ghost en `text-brand`) que salta a su paso, y "Sin capturar" en muted cuando falta un dato. RFC, CURP y NSS en mono.
- Pie del formulario (`bg-surface-sunken border-t border-line px-6 py-4`): "← Anterior" (secundario, deshabilitado en el paso 1) a la izquierda; "Guardar borrador" (ghost) y "Siguiente →" (primario) a la derecha. En el último paso, "✓ Crear expediente".
- Comportamiento:
  - Todos los campos quedan montados (los pasos se ocultan, no se desmontan) para no perder lo capturado.
  - "Siguiente" valida en el cliente los obligatorios del paso actual. Si falta alguno, marca el error (p. ej. "Ingresa el nombre del colaborador para continuar.") y enfoca el primer campo inválido.
  - Se puede saltar a pasos anteriores desde el Stepper. A pasos posteriores solo si los anteriores son válidos.
  - El envío final es un POST a `empleados.store` con un **FormRequest** que valida RFC (`^[A-ZÑ&]{4}\d{6}[A-Z\d]{3}$`), CURP (`^[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z\d]\d$`), NSS (`^\d{11}$`), CP (`^\d{5}$`) y teléfono (`^\d{10}$`), y convierte a mayúsculas antes de validar. Si el servidor devuelve errores, lleva al usuario al primer paso con error.
  - "Guardar borrador": si no existe soporte, guarda en `localStorage` con la llave `empleado-draft` y lo restaura al volver; se borra al crear el expediente.
  - Reutiliza este mismo formulario para **Editar empleado**.
- Si faltan columnas en la tabla de empleados (apellidos separados, estado civil, régimen, domicilio, jornada, jefe directo, tipo de contrato, salario diario), crea una migración **aditiva** (columnas `nullable`) y ajusta `$fillable` y el factory.

---

## 6. Modo claro / oscuro

- Toda la UI usa **solo tokens** (`bg-surface`, `text-ink`…). Nada de `bg-white`, `text-gray-*` ni `dark:` sueltos en las pantallas nuevas: el cambio lo hacen las variables de `.dark`.
- El ThemeToggle aparece en la topbar de la app y en la esquina superior de login/registro. Respeta la preferencia del sistema en la primera visita y persiste la elección.
- Agrega `color-scheme: dark` (ya incluido en `.dark`) para que los date pickers y selects nativos se vean oscuros.
- Evita el parpadeo: el script inline del starter kit que pone `.dark` antes de pintar debe seguir en el `<head>`.

---

## 7. Fases y criterios de aceptación

1. **Tokens + componentes base** (secciones 2–3). ✔ Build sin errores; una ruta `/_ui` (solo en `local`) muestra todos los componentes en ambos temas.
2. **Layout** (sección 4). ✔ Navegación activa correcta; el sidebar colapsa en móvil.
3. **Auth** (5.1–5.2). ✔ Login, registro en 2 pasos, reset de contraseña y verificación de correo con el mismo estilo; las pruebas de auth pasan.
4. **Empleados índice** (5.4). ✔ Filtros por URL, paginación, búsqueda con debounce, estado vacío y exportación CSV. Agrega un Feature test de los filtros.
5. **Nuevo/Editar empleado** (5.5). ✔ Validación por paso y del servidor, borrador y revisión. Agrega Feature tests de store/update con RFC/CURP válidos e inválidos.
6. **Dashboard** (5.3). ✔ Métricas reales y estados vacíos.
7. **Revisión final**: contraste AA en ambos temas, navegación completa con teclado, `php artisan test` y `npm run build` en verde, y ningún color hardcodeado fuera de `app.css`.

Al terminar cada fase, resume qué archivos cambiaste y qué decisiones tomaste cuando el código existente no coincidía con este documento.

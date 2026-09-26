@props(['head'])

<div {{ $attributes->merge(['class' => 'bg-surface-raised border border-line rounded-xl shadow-sm overflow-hidden']) }}>
    {{ $toolbar ?? '' }}

    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr>
                    {{ $head }}
                </tr>
            </thead>
            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>

    {{ $empty ?? '' }}
    {{ $footer ?? '' }}
</div>

@props(['steps'])

<nav aria-label="Pasos" {{ $attributes->merge(['class' => 'bg-surface-raised border border-line rounded-xl shadow-sm p-3']) }}>
    <ol class="flex flex-col">
        @foreach ($steps as $i => $step)
            <li class="relative">
                <button
                    type="button"
                    @click="goTo({{ $i }})"
                    :aria-current="step === {{ $i }} ? 'step' : false"
                    class="w-full flex items-start gap-3 p-3 rounded-lg text-left cursor-pointer text-ink"
                    :class="step === {{ $i }} ? 'bg-surface-sunken' : ''"
                >
                    <span
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-semibold border-2"
                        :class="{
                            'bg-brand text-on-brand border-brand': step > {{ $i }},
                            'bg-brand-soft text-brand border-brand': step === {{ $i }},
                            'bg-surface-raised text-ink-muted border-line-strong': step < {{ $i }},
                        }"
                    >
                        <template x-if="step > {{ $i }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m4.5 12.75 6 6 9-13.5"></path></svg>
                        </template>
                        <template x-if="step <= {{ $i }}">
                            <span>{{ $i + 1 }}</span>
                        </template>
                    </span>

                    <span class="flex flex-col gap-0.5 pt-1">
                        <span class="text-sm font-semibold" :class="step >= {{ $i }} ? 'text-ink' : 'text-ink-muted'">{{ $step['title'] }}</span>
                        <span class="text-xs text-ink-muted">{{ $step['desc'] }}</span>
                    </span>
                </button>

                @if (! $loop->last)
                    <span aria-hidden="true" class="absolute left-[27px] top-[50px] w-0.5 h-[18px] rounded" :class="step > {{ $i }} ? 'bg-brand' : 'bg-line'"></span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>

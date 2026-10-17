<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => __('Dashboard')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Dashboard'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\App\Support\AccountFeatures::checklist()): ?>
    <?php
        // Compute first-run setup state. All three cards always render so the
        // user can see at a glance what's connected vs. what's still pending,
        // AND keep adding more (more email accounts, more contacts, tweak AI).
        // Connected cards show a green check + a count + the action link
        // becomes "Connect another" / "Import more" / "Tune it" instead of
        // being struck through.
        $wsId = auth()->user()?->active_workspace_id;

        $emailCount = $wsId
            ? \App\Models\EmailAccount::where('workspace_id', $wsId)->where('status', 'connected')->count()
            : 0;
        // Pluck the connected provider slugs so each card can render a small
        // row of brand-style badges (Gmail / Outlook / IMAP / Custom).
        $emailProviders = $wsId
            ? \App\Models\EmailAccount::where('workspace_id', $wsId)->where('status', 'connected')->pluck('provider')->unique()->values()->all()
            : [];
        $hasEmail = $emailCount > 0;

        // AI is "ready" only when a usable API key is resolvable — either
        // the workspace stored its own key, or the platform has a global
        // provider key configured. The default ai_configs row created
        // during onboarding shouldn't, by itself, mark AI as ready.
        $hasAiConfig = false;
        if ($wsId) {
            $aiConfig = \App\Models\AiConfig::where('workspace_id', $wsId)->first();
            if ($aiConfig) {
                $ownKey = $aiConfig->use_own_key && !empty($aiConfig->getRawOriginal('api_key'));
                $platformKey = !empty(config('services.openai.api_key'))
                    || !empty(config('services.anthropic.api_key'))
                    || !empty(config('services.gemini.api_key'))
                    || !empty(config('services.mistral.api_key'));
                $hasAiConfig = $ownKey || (!$aiConfig->use_own_key && $platformKey);
            }
        }

        $contactCount = $wsId
            ? \App\Models\Contact::where('workspace_id', $wsId)->count()
            : 0;
        $hasContacts = $contactCount > 0;

        $tourSteps = [
            [
                'key' => 'email',
                'href' => url('/settings/email'),
                'title' => __('Connect Email'),
                'subtitle' => __('Link your inbox'),
                'done' => $hasEmail,
                'done_title' => __('Email Connected'),
                'done_subtitle' => trans_choice(':count account connected|:count accounts connected — connect more', $emailCount, ['count' => $emailCount]),
                'done_cta' => __('Add another account'),
                'providers' => $emailProviders,
                'svg' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
            ],
            [
                'key' => 'ai',
                'href' => url('/settings/ai'),
                'title' => __('Train Your AI'),
                'subtitle' => __('Configure responses'),
                'done' => $hasAiConfig,
                'done_title' => __('AI Ready'),
                'done_subtitle' => __('Configured — keep refining responses'),
                'done_cta' => __('Tune settings'),
                'providers' => [],
                'svg' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
            ],
            [
                'key' => 'contacts',
                'href' => url('/contacts'),
                'title' => __('Import Contacts'),
                'subtitle' => __('Add your people'),
                'done' => $hasContacts,
                'done_title' => __('Contacts Imported'),
                'done_subtitle' => trans_choice(':count contact — import more|:count contacts — import more', $contactCount, ['count' => number_format($contactCount)]),
                'done_cta' => __('Import more'),
                'providers' => [],
                'svg' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
            ],
        ];
        $doneCount = collect($tourSteps)->where('done', true)->count();

        // Small inline SVG badges for each provider slug — keeps the "which
        // channel is connected" visual without pulling a whole icon library.
        $providerBadge = function (string $p): string {
            $cls = 'w-3.5 h-3.5';
            return match ($p) {
                'gmail' => '<svg class="'.$cls.'" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 4-8 5-8-5V6l8 5 8-5z"/></svg>',
                'outlook' => '<svg class="'.$cls.'" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18zm0 4a5 5 0 1 1 0 10 5 5 0 0 1 0-10z"/></svg>',
                'imap', 'custom' => '<svg class="'.$cls.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 5L2 7"/></svg>',
                default => '<svg class="'.$cls.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>',
            };
        };
        $providerLabel = fn (string $p) => match ($p) {
            'gmail' => 'Gmail', 'outlook' => 'Outlook',
            'imap' => 'IMAP', 'custom' => 'Custom',
            default => ucfirst($p),
        };
    ?>

    
    <div
        x-data="{ collapsed: localStorage.getItem('mailtrixy_tour_collapsed') === 'true' }"
        x-cloak
        class="mb-6"
    >
        <div class="relative overflow-hidden rounded-xl bg-gradient-to-r from-primary-600 to-indigo-500 p-6 text-white shadow-lg">
            
            <button
                @click="collapsed = !collapsed; localStorage.setItem('mailtrixy_tour_collapsed', collapsed ? 'true' : 'false')"
                :aria-label="collapsed ? '<?php echo e(__('Expand welcome panel')); ?>' : '<?php echo e(__('Collapse welcome panel')); ?>'"
                class="absolute top-3 right-3 p-1.5 rounded-lg text-white/70 hover:text-white hover:bg-surface/10 transition-colors"
            >
                <svg class="w-4 h-4 transition-transform duration-200" :class="collapsed ? '-rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <h2 class="text-lg font-semibold mb-1"><?php echo e(__('Welcome to')); ?> <?php echo e(config('app.name')); ?>!</h2>
            <p class="text-sm text-white/80" :class="collapsed ? 'mb-0' : 'mb-5'">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($doneCount === 0): ?>
                    <?php echo e(__('Here are your first steps to get up and running:')); ?>

                <?php elseif($doneCount < count($tourSteps)): ?>
                    <?php echo e(__('Setup progress')); ?>: <span class="font-semibold text-white"><?php echo e($doneCount); ?>/<?php echo e(count($tourSteps)); ?></span> — <?php echo e(__('keep going!')); ?>

                <?php else: ?>
                    <?php echo e(__('You\'re all set — keep growing your workspace.')); ?>

                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </p>

            <div x-show="!collapsed" x-collapse class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $tourSteps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <a href="<?php echo e($step['href']); ?>"
                   class="relative flex items-start gap-3 rounded-lg px-4 py-3 bg-surface-2/10 hover:bg-surface/20 transition-colors group">
                    <div class="flex-shrink-0 h-10 w-10 rounded-lg flex items-center justify-center <?php echo e($step['done'] ? 'bg-emerald-400/20 text-emerald-200' : 'bg-surface-2/20'); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step['done']): ?>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <?php else: ?>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($step['svg']); ?>"/></svg>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="text-sm font-medium leading-tight"><?php echo e($step['done'] ? $step['done_title'] : $step['title']); ?></p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step['done']): ?>
                                <span class="text-[10px] font-semibold uppercase tracking-wide bg-emerald-400/25 text-emerald-50 px-1.5 py-0.5 rounded inline-flex items-center gap-1">
                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    <?php echo e(__('Connected')); ?>

                                </span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <p class="text-xs text-white/70 leading-tight mt-0.5"><?php echo e($step['done'] ? $step['done_subtitle'] : $step['subtitle']); ?></p>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step['done'] && ! empty($step['providers'])): ?>
                            
                            <div class="flex flex-wrap items-center gap-1 mt-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $step['providers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-medium bg-white/10 text-white/90 px-1.5 py-0.5 rounded">
                                        <?php echo $providerBadge($p); ?>

                                        <?php echo e($providerLabel($p)); ?>

                                    </span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <div class="mt-1.5 inline-flex items-center gap-1 text-[11px] font-semibold text-white/90 group-hover:text-white">
                            <span><?php echo e($step['done'] ? $step['done_cta'] : __('Get started')); ?></span>
                            <svg class="w-3 h-3 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </div>
                </a>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>
    </div>

    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div x-data="{ loaded: false }" x-init="
        const observer = new MutationObserver((mutations) => {
            for (const m of mutations) {
                for (const node of m.addedNodes) {
                    if (node.nodeType === 1 && node.hasAttribute('wire:id')) {
                        loaded = true;
                        observer.disconnect();
                        return;
                    }
                }
            }
        });
        observer.observe($el, { childList: true, subtree: true });
        
        setTimeout(() => { loaded = true; observer.disconnect(); }, 5000);
    ">
        
        <div x-show="!loaded" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="space-y-6" role="status" :aria-busy="!loaded ? 'true' : 'false'" aria-label="<?php echo e(__('Loading dashboard')); ?>">
            
            <div class="flex items-center justify-between">
                <div class="h-8 w-48 bg-gray-200 dark:bg-gray-700 rounded-lg animate-pulse"></div>
                <div class="h-10 w-32 bg-gray-200 dark:bg-gray-700 rounded-lg animate-pulse"></div>
            </div>

            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 0; $i < 4; $i++): ?>
                <div class="bg-surface-2  rounded-xl border border-border dark:border-gray-700 p-6 animate-pulse">
                    <div class="flex items-center justify-between mb-4">
                        <div class="h-4 w-24 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        <div class="h-10 w-10 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                    </div>
                    <div class="h-8 w-20 bg-gray-200 dark:bg-gray-700 rounded mb-2"></div>
                    <div class="h-3 w-32 bg-gray-200 dark:bg-gray-700 rounded"></div>
                </div>
                <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 0; $i < 2; $i++): ?>
                <div class="bg-surface-2  rounded-xl border border-border dark:border-gray-700 p-6 animate-pulse">
                    <div class="flex items-center justify-between mb-6">
                        <div class="h-5 w-36 bg-gray-200 dark:bg-gray-700 rounded"></div>
                        <div class="h-8 w-24 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                    </div>
                    <div class="h-48 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                </div>
                <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-surface-2  rounded-xl border border-border dark:border-gray-700 p-6 animate-pulse">
                    <div class="h-5 w-32 bg-gray-200 dark:bg-gray-700 rounded mb-4"></div>
                    <div class="space-y-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 0; $i < 5; $i++): ?>
                        <div class="flex items-center gap-3">
                            <div class="h-9 w-9 bg-gray-200 dark:bg-gray-700 rounded-full flex-shrink-0"></div>
                            <div class="flex-1 space-y-2">
                                <div class="h-3.5 w-3/4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                <div class="h-3 w-1/2 bg-gray-200 dark:bg-gray-700 rounded"></div>
                            </div>
                        </div>
                        <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
                <div class="bg-surface-2  rounded-xl border border-border dark:border-gray-700 p-6 animate-pulse">
                    <div class="h-5 w-28 bg-gray-200 dark:bg-gray-700 rounded mb-4"></div>
                    <div class="space-y-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 0; $i < 4; $i++): ?>
                        <div class="h-12 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                        <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        
        <div x-show="loaded" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('dashboard.dashboard-stats', []);

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-2578851719-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
        </div>
    </div>

    
    <div class="mt-6">
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('dashboard.actionable-insights', []);

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-2578851719-1', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $attributes = $__attributesOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__attributesOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $component = $__componentOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__componentOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?><?php /**PATH /home/dahimail.com/public_html/resources/views/dashboard/index.blade.php ENDPATH**/ ?>
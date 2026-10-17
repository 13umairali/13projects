<div class="space-y-6 text-ink relative">
    
    <div wire:loading class="absolute top-2 right-2 z-10">
        <svg class="animate-spin h-4 w-4 text-brand" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
    </div>

    
    <div class="panel p-6">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-ink"><?php echo e(__('Welcome back, :name', ['name' => auth()->user()->name ?? __('there')])); ?></h1>
                <p class="text-sm text-muted mt-1"><?php echo e(__("Here's what's happening with your workspace.")); ?></p>
            </div>
            <div class="flex gap-2">
                <a href="<?php echo e(url('/inbox')); ?>" wire:navigate class="btn-primary gap-2 text-sm">
                    <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'inbox','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'inbox','class' => 'w-4 h-4']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
                    <?php echo e(__('Open Inbox')); ?>

                </a>
                <a href="<?php echo e(url('/settings/email')); ?>" wire:navigate class="btn-secondary gap-2 text-sm">
                    <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'plus','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'plus','class' => 'w-4 h-4']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
                    <?php echo e(__('Connect Account')); ?>

                </a>
            </div>
        </div>
    </div>

    

    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <?php
        $stats = [
            ['label' => __('Total Conversations'), 'value' => number_format($conversationCount), 'period' => __('all time'), 'icon' => 'inbox', 'gradient' => 'from-blue-50 to-indigo-50', 'gradient_dark' => 'dark:from-blue-900/20 dark:to-indigo-900/20', 'icon_bg' => 'bg-info/15', 'icon_bg_dark' => 'dark:bg-blue-900/40', 'icon_color' => 'text-blue-600', 'icon_color_dark' => 'dark:text-blue-400', 'bar_color' => 'bg-blue-200', 'bar_color_dark' => 'dark:bg-blue-800/60'],
            ['label' => __('AI Replies Sent'), 'value' => number_format($aiRepliesCount), 'period' => __('all time'), 'icon' => 'brain', 'gradient' => 'from-purple-50 to-violet-50', 'gradient_dark' => 'dark:from-purple-900/20 dark:to-violet-900/20', 'icon_bg' => 'bg-brand/15', 'icon_bg_dark' => 'dark:bg-purple-900/40', 'icon_color' => 'text-purple-600', 'icon_color_dark' => 'dark:text-purple-400', 'bar_color' => 'bg-purple-200', 'bar_color_dark' => 'dark:bg-purple-800/60'],
            ['label' => __('Avg Response Time'), 'value' => $avgResponseTimeDisplay, 'period' => __('average'), 'icon' => 'clock', 'gradient' => 'from-cyan-50 to-teal-50', 'gradient_dark' => 'dark:from-cyan-900/20 dark:to-teal-900/20', 'icon_bg' => 'bg-cyan-100', 'icon_bg_dark' => 'dark:bg-cyan-900/40', 'icon_color' => 'text-cyan-600', 'icon_color_dark' => 'dark:text-cyan-400', 'bar_color' => 'bg-cyan-200', 'bar_color_dark' => 'dark:bg-cyan-800/60'],
            ['label' => __('Total Contacts'), 'value' => number_format($contactCount), 'period' => __('all time'), 'icon' => 'message-square', 'gradient' => 'from-amber-50 to-orange-50', 'gradient_dark' => 'dark:from-amber-900/20 dark:to-orange-900/20', 'icon_bg' => 'bg-warning/15', 'icon_bg_dark' => 'dark:bg-amber-900/40', 'icon_color' => 'text-amber-600', 'icon_color_dark' => 'dark:text-amber-400', 'bar_color' => 'bg-amber-200', 'bar_color_dark' => 'dark:bg-amber-800/60'],
        ];
        ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
        <div class="relative bg-gradient-to-br <?php echo e($stat['gradient']); ?> <?php echo e($stat['gradient_dark']); ?> rounded-2xl border border-border overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 group bg-surface-2 dark:bg-gray-950">
            <div class="absolute -right-3 -bottom-3 opacity-[0.04] dark:opacity-[0.05] group-hover:opacity-[0.08] dark:group-hover:opacity-[0.1] transition-opacity duration-300">
                <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => $stat['icon'],'class' => 'w-24 h-24 '.e($stat['icon_color']).' '.e($stat['icon_color_dark']).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stat['icon']),'class' => 'w-24 h-24 '.e($stat['icon_color']).' '.e($stat['icon_color_dark']).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
            </div>
            <div class="relative p-5">
                <div class="flex flex-row items-center justify-between mb-3">
                    <div class="w-10 h-10 <?php echo e($stat['icon_bg']); ?> <?php echo e($stat['icon_bg_dark']); ?> rounded-xl flex items-center justify-center shadow-sm">
                        <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => $stat['icon'],'class' => 'w-5 h-5 '.e($stat['icon_color']).' '.e($stat['icon_color_dark']).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stat['icon']),'class' => 'w-5 h-5 '.e($stat['icon_color']).' '.e($stat['icon_color_dark']).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
                    </div>
                    <span class="text-xs font-medium text-muted"><?php echo e($stat['label']); ?></span>
                </div>
                <div class="text-3xl font-bold text-ink"><?php echo e($stat['value']); ?></div>
                <div class="flex items-center gap-1 text-xs mt-1 text-muted font-medium">
                    <?php echo e($stat['period']); ?>

                </div>
                <div class="flex items-end gap-0.5 mt-3 h-5">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $sparklineHeights; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <div class="flex-1 <?php echo e($stat['bar_color']); ?> <?php echo e($stat['bar_color_dark']); ?> rounded-sm" style="height: <?php echo e($h); ?>%"></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </div>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($recentConversations->isNotEmpty()): ?>
    <div class="bg-surface rounded-2xl border border-border p-4 mt-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-ink"><?php echo e(__('Continue where you left off')); ?></h3>
            <a href="<?php echo e(url('/inbox')); ?>" wire:navigate class="text-xs text-brand hover:text-brand-strong font-medium transition-colors"><?php echo e(__('View All')); ?></a>
        </div>
        <div class="space-y-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $recentConversations->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
            <a href="<?php echo e(url('/inbox?cid=' . $conv->id)); ?>" wire:navigate class="flex items-center gap-3 p-2 -mx-2 rounded-lg hover:bg-surface-2 transition-colors group">
                <div class="w-8 h-8 rounded-full bg-brand/10 text-brand flex items-center justify-center text-xs font-bold shrink-0">
                    <?php echo e(strtoupper(substr($conv->contact?->first_name ?? $conv->subject ?? '?', 0, 1))); ?>

                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-ink truncate group-hover:text-brand transition-colors"><?php echo e($conv->contact?->full_name ?? $conv->subject ?? __('Conversation')); ?></p>
                    <p class="text-xs text-muted truncate"><?php echo e($conv->last_message_preview ?? __('No messages')); ?></p>
                </div>
                <span class="text-xs text-muted shrink-0" title="<?php echo e($conv->last_message_at?->format('M j, Y g:i A')); ?>"><?php echo e($conv->last_message_at?->diffForHumans() ?? ''); ?></span>
            </a>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$hasKbDocs || !$hasSentAiReply || !$hasChannels): ?>
    <div class="bg-brand/5 rounded-2xl border border-brand/10 p-4 mt-4">
        <h3 class="text-sm font-semibold text-ink mb-2"><?php echo e(__('Unlock more features')); ?></h3>
        <div class="space-y-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$hasKbDocs): ?>
            <a href="<?php echo e(url('/knowledge-base')); ?>" wire:navigate class="flex items-center gap-2 text-sm text-muted hover:text-brand transition-colors">
                <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                <?php echo e(__('Train AI with your knowledge base for smarter replies')); ?>

            </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$hasSentAiReply): ?>
            <a href="<?php echo e(url('/inbox')); ?>" wire:navigate class="flex items-center gap-2 text-sm text-muted hover:text-brand transition-colors">
                <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                <?php echo e(__('Try AI-powered replies on your next email')); ?>

            </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$hasChannels): ?>
            <a href="<?php echo e(url('/settings/channels')); ?>" wire:navigate class="flex items-center gap-2 text-sm text-muted hover:text-brand transition-colors">
                <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <?php echo e(__('Connect WhatsApp, Telegram, or SMS channels')); ?>

            </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php
        $steps = [
            ['done' => $hasEmailAccount, 'title' => __('Connect your email account'), 'desc' => __('Link Gmail, Outlook, or other email providers'), 'href' => url('/settings/email'), 'icon' => 'mail', 'iconBg' => 'bg-danger/15 text-danger', 'iconBgDark' => 'dark:bg-red-900/40 dark:text-red-400'],
            ['done' => $hasAiConfig, 'title' => __('Add your AI provider key'), 'desc' => __('Connect OpenAI, Claude, or Gemini'), 'href' => url('/settings/ai'), 'icon' => 'sparkles', 'iconBg' => 'bg-brand/15 text-purple-600', 'iconBgDark' => 'dark:bg-purple-900/40 dark:text-purple-400'],
            ['done' => $hasAiConfig ?? false, 'title' => __('Configure AI agents'), 'desc' => __('Set up context and response styles'), 'href' => url('/settings/ai'), 'icon' => 'bot', 'iconBg' => 'bg-brand/15 text-brand', 'iconBgDark' => 'dark:bg-indigo-900/40 dark:text-indigo-400'],
            ['done' => $hasKbDocs ?? false, 'title' => __('Train knowledge base'), 'desc' => __('Upload docs or scrape website'), 'href' => url('/knowledge-base'), 'icon' => 'book-open', 'iconBg' => 'bg-warning/15 text-amber-600', 'iconBgDark' => 'dark:bg-amber-900/40 dark:text-amber-400'],
            ['done' => $hasChannels, 'title' => __('Connect more channels'), 'desc' => __('WhatsApp, SMS, Telegram, Slack'), 'href' => url('/settings/channels'), 'icon' => 'globe', 'iconBg' => 'bg-cyan-100 text-cyan-600', 'iconBgDark' => 'dark:bg-cyan-900/40 dark:text-cyan-400'],
            ['done' => $hasTeamMembers, 'title' => __('Invite your team'), 'desc' => __('Add users and assignment rules'), 'href' => url('/settings/team'), 'icon' => 'users', 'iconBg' => 'bg-info/15 text-blue-600', 'iconBgDark' => 'dark:bg-blue-900/40 dark:text-blue-400'],
        ];
        $completedCount = collect($steps)->where('done', true)->count();
        $totalSteps = count($steps);
    ?>

    <div class="grid grid-cols-1 <?php echo e(\App\Support\AccountFeatures::checklist() ? 'lg:grid-cols-3' : ''); ?> gap-6">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\App\Support\AccountFeatures::checklist()): ?>
        
        <div class="lg:col-span-2 bg-surface-2 rounded-2xl border border-border shadow-sm">
            <div class="px-6 pt-6 pb-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900/40 rounded-lg flex items-center justify-center">
                            <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'list-checks','class' => 'w-4 h-4 text-primary-600 dark:text-primary-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'list-checks','class' => 'w-4 h-4 text-primary-600 dark:text-primary-400']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-ink"><?php echo e(__('Getting Started')); ?></h2>
                            <p class="text-sm text-muted mt-0.5"><?php echo e(__('Complete these steps to get the most out of MailTrixy')); ?></p>
                        </div>
                    </div>
                    <span class="text-sm font-bold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/30 px-3 py-1 rounded-full border border-primary-100 dark:border-primary-800">
                        <?php echo e($completedCount); ?>/<?php echo e($totalSteps); ?>

                    </span>
                </div>
                
                <div class="bg-gradient-to-r from-gray-50 dark:from-gray-800/50 to-gray-100/50 dark:to-gray-800/30 rounded-xl p-4 border border-border mt-4">
                    <div class="flex justify-between text-xs mb-2">
                        <span class="font-semibold text-ink/80"><?php echo e(__(':completed of :total completed', ['completed' => $completedCount, 'total' => $totalSteps])); ?></span>
                        <span class="font-bold text-primary-600 dark:text-primary-400"><?php echo e(round(($completedCount / $totalSteps) * 100)); ?>%</span>
                    </div>
                    <div class="w-full h-2.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-2.5 bg-gradient-to-r from-primary-500 to-primary-600 dark:from-primary-600 dark:to-primary-500 rounded-full transition-all duration-700 shadow-sm shadow-primary-200 dark:shadow-none" style="width: <?php echo e(($completedCount / $totalSteps) * 100); ?>%"></div>
                    </div>
                </div>
            </div>
            <div class="px-6 pb-6">
                <div class="space-y-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <a href="<?php echo e($step['href']); ?>"
                       class="flex items-center gap-4 p-3.5 rounded-xl border transition-all duration-200 hover:shadow-sm hover:-translate-y-0.5
                              <?php echo e($step['done'] ? 'border-success/20 dark:border-green-900/50 bg-success/10/50 dark:bg-green-900/10' : 'border-border hover:bg-surface dark:hover:bg-surface/50 hover:border-border dark:hover:border-gray-700'); ?>">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 transition-all duration-300
                                    <?php echo e($step['done'] ? 'bg-gradient-to-br from-green-500 to-emerald-500 text-white shadow-sm shadow-green-200 dark:shadow-none' : $step['iconBg'] . ' ' . $step['iconBgDark']); ?>">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($step['done']): ?>
                            <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'check-circle','class' => 'w-4.5 h-4.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check-circle','class' => 'w-4.5 h-4.5']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
                            <?php else: ?>
                            <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => $step['icon'],'class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($step['icon']),'class' => 'w-4 h-4']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-sm <?php echo e($step['done'] ? 'line-through text-muted' : 'text-ink'); ?>"><?php echo e($step['title']); ?></p>
                            <p class="text-xs text-muted mt-0.5"><?php echo e($step['desc']); ?></p>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$step['done']): ?>
                        <span class="shrink-0 text-xs font-semibold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/40 px-3 py-1.5 rounded-lg hover:bg-primary-100 dark:hover:bg-primary-900/60 transition-colors flex items-center gap-1">
                            <?php echo e(__('Start')); ?>

                            <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'arrow-right','class' => 'w-3 h-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-right','class' => 'w-3 h-3']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
                        </span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </div>
        </div>

        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="bg-surface-2 rounded-2xl border border-border shadow-sm">
            <div class="px-6 pt-6 pb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 bg-secondary-100 dark:bg-secondary-900/40 rounded-lg flex items-center justify-center">
                        <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'globe','class' => 'w-4 h-4 text-secondary-600 dark:text-secondary-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'globe','class' => 'w-4 h-4 text-secondary-600 dark:text-secondary-400']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-ink"><?php echo e(__('Connect Channels')); ?></h2>
                        <p class="text-sm text-muted mt-0.5"><?php echo e(__('Set up your communication channels')); ?></p>
                    </div>
                </div>
            </div>
            <div class="px-6 pb-6">
                <div class="space-y-2">
                    <?php
                    $channels = [
                        ['name' => __('Email'), 'icon' => 'mail', 'href' => url('/settings/email'), 'color' => 'text-red-500 dark:text-red-400', 'bg' => 'bg-danger/10 dark:bg-red-900/20', 'hover' => 'hover:border-danger/20 dark:hover:border-red-900 hover:bg-danger/10/50 dark:hover:bg-red-900/10'],
                        ['name' => __('WhatsApp'), 'icon' => 'message-circle', 'href' => url('/settings/channels'), 'color' => 'text-green-500 dark:text-green-400', 'bg' => 'bg-success/10 dark:bg-green-900/20', 'hover' => 'hover:border-success/20 dark:hover:border-green-900 hover:bg-success/10/50 dark:hover:bg-green-900/10'],
                        ['name' => __('SMS'), 'icon' => 'phone', 'href' => url('/settings/channels'), 'color' => 'text-blue-500 dark:text-blue-400', 'bg' => 'bg-info/10 dark:bg-blue-900/20', 'hover' => 'hover:border-info/20 dark:hover:border-blue-900 hover:bg-info/10/50 dark:hover:bg-blue-900/10'],
                        ['name' => __('Telegram'), 'icon' => 'send', 'href' => url('/settings/channels'), 'color' => 'text-sky-500 dark:text-sky-400', 'bg' => 'bg-sky-50 dark:bg-sky-900/20', 'hover' => 'hover:border-sky-200 dark:hover:border-sky-900 hover:bg-sky-50/50 dark:hover:bg-sky-900/10'],
                        ['name' => __('Slack'), 'icon' => 'hash', 'href' => url('/settings/channels'), 'color' => 'text-purple-500 dark:text-purple-400', 'bg' => 'bg-brand/10 dark:bg-purple-900/20', 'hover' => 'hover:border-purple-200 dark:hover:border-purple-900 hover:bg-brand/10/50 dark:hover:bg-purple-900/10'],
                        ['name' => __('Live Chat'), 'icon' => 'message-square', 'href' => url('/settings/channels'), 'color' => 'text-orange-500 dark:text-orange-400', 'bg' => 'bg-orange-50 dark:bg-orange-900/20', 'hover' => 'hover:border-orange-200 dark:hover:border-orange-900 hover:bg-orange-50/50 dark:hover:bg-orange-900/10'],
                    ];
                    ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $channels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <a href="<?php echo e($ch['href']); ?>"
                       class="flex items-center gap-3 p-3.5 rounded-xl border border-border <?php echo e($ch['hover']); ?> hover:shadow-sm hover:-translate-y-0.5 transition-all duration-200 group">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center <?php echo e($ch['bg']); ?> shadow-sm">
                            <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => $ch['icon'],'class' => 'w-[18px] h-[18px] '.e($ch['color']).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ch['icon']),'class' => 'w-[18px] h-[18px] '.e($ch['color']).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-ink"><?php echo e($ch['name']); ?></p>
                        </div>
                        <div class="w-7 h-7 bg-surface  group-hover:bg-primary-100 dark:group-hover:bg-primary-900/50 rounded-full flex items-center justify-center transition-colors">
                            <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'plus','class' => 'w-3.5 h-3.5 text-muted  group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'plus','class' => 'w-3.5 h-3.5 text-muted  group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
                        </div>
                    </a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    
    <div class="bg-surface-2 rounded-2xl border border-border shadow-sm">
        <div class="flex flex-row items-center justify-between px-6 pt-6 pb-4">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 bg-info/15 dark:bg-blue-900/40 rounded-lg flex items-center justify-center">
                    <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'inbox','class' => 'w-4 h-4 text-blue-600 dark:text-blue-400']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'inbox','class' => 'w-4 h-4 text-blue-600 dark:text-blue-400']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-ink"><?php echo e(__('Recent Conversations')); ?></h2>
                    <p class="text-sm text-muted mt-0.5"><?php echo e(__('Your latest email and message threads')); ?></p>
                </div>
            </div>
            <a href="<?php echo e(url('/inbox')); ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 bg-primary-50 dark:bg-primary-900/30 rounded-lg hover:bg-primary-100 dark:hover:bg-primary-900/50 transition-all duration-200">
                <?php echo e(__('View All')); ?> <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'arrow-right','class' => 'w-3.5 h-3.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-right','class' => 'w-3.5 h-3.5']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
            </a>
        </div>
        <div class="px-6 pb-6">
            <div class="space-y-1">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recentConversations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <a href="<?php echo e(url('/inbox?cid=' . $conv->id)); ?>"
                   class="flex items-center gap-3 p-3.5 rounded-xl hover:bg-surface transition-all duration-200 group border border-transparent hover:border-border dark:hover:border-gray-700">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-700 flex items-center justify-center shrink-0 shadow-sm">
                        <span class="text-xs font-semibold text-muted /50"><?php echo e($conv->contact?->initials ?? '??'); ?></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm truncate <?php echo e(!$conv->is_read ? 'font-bold text-ink' : 'font-medium text-ink/80'); ?>"><?php echo e($conv->subject ?? __('No subject')); ?></p>
                        <p class="text-xs text-muted"><?php echo e($conv->contact?->full_name ?? $conv->contact?->email ?? __('Unknown')); ?></p>
                    </div>
                    <div class="flex items-center gap-2.5 shrink-0">
                        <span class="badge badge-sm <?php echo e(match($conv->status) { 'open' => 'badge-info', 'closed' => 'badge-ghost', 'snoozed' => 'badge-warning', 'pending' => 'badge-warning', default => 'badge-ghost' }); ?>">
                            <?php echo e(ucfirst($conv->status)); ?>

                        </span>
                        <span class="text-xs text-muted  font-medium"><?php echo e($conv->last_message_at?->diffForHumans(null, true) ?? ''); ?></span>
                    </div>
                </a>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <div class="py-12 text-center">
                    <svg class="w-12 h-12 text-muted/50 /50 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <p class="text-sm font-medium text-ink"><?php echo e(__('No conversations yet')); ?></p>
                    <p class="text-xs text-muted mt-1"><?php echo e(__('Connect an email account or channel to start receiving messages.')); ?></p>
                    <a href="<?php echo e(url('/settings/email')); ?>" class="inline-flex items-center gap-1.5 mt-3 px-4 py-2 text-sm font-medium text-white bg-primary-600 dark:bg-primary-700 rounded-xl hover:bg-primary-700 dark:hover:bg-primary-600 transition-colors">
                        <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'mail','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'mail','class' => 'w-4 h-4']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
                        <?php echo e(__('Connect Email')); ?>

                    </a>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /home/dahimail.com/public_html/resources/views/livewire/dashboard/dashboard-stats.blade.php ENDPATH**/ ?>
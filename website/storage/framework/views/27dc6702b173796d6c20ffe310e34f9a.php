<?php if (isset($component)) { $__componentOriginalc8c9fd5d7827a77a31381de67195f0c3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc8c9fd5d7827a77a31381de67195f0c3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.admin','data' => ['title' => __('AI Usage'),'subtitle' => __('Monitor AI consumption, costs, and performance across all workspaces.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.admin'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('AI Usage')),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Monitor AI consumption, costs, and performance across all workspaces.'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div class="space-y-6">

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($stats->total_requests ?? 0) === 0): ?>
            
            <div class="panel p-12 text-center">
                <div class="w-16 h-16 bg-brand/15 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-ink mb-1"><?php echo e(__('No AI usage data yet')); ?></h3>
                <p class="text-sm text-muted max-w-md mx-auto"><?php echo e(__('AI usage metrics will appear here once workspaces start using AI features like smart replies, sentiment analysis, and embeddings.')); ?></p>
            </div>
        <?php else: ?>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="panel p-5 hover:shadow-md transition-all duration-200">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-soft">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        </div>
                    </div>
                    <p class="text-2xl font-extrabold text-ink"><?php echo e(number_format($stats->total_requests)); ?></p>
                    <p class="text-xs text-muted mt-1"><?php echo e(__('Total AI Requests')); ?></p>
                </div>

                <div class="panel p-5 hover:shadow-md transition-all duration-200">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-200">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <p class="text-2xl font-extrabold text-ink"><?php echo \App\Helpers\CurrencyHelper::display($stats->total_cost); ?></p>
                    <p class="text-xs text-muted mt-1"><?php echo e(__('Total AI Cost')); ?></p>
                </div>

                <div class="panel p-5 hover:shadow-md transition-all duration-200">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg shadow-amber-200">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                        </div>
                    </div>
                    <?php
                        $totalTokens = $stats->total_tokens;
                        $tokenDisplay = $totalTokens >= 1000000
                            ? number_format($totalTokens / 1000000, 1) . 'M'
                            : ($totalTokens >= 1000 ? number_format($totalTokens / 1000, 1) . 'K' : number_format($totalTokens));
                    ?>
                    <p class="text-2xl font-extrabold text-ink"><?php echo e($tokenDisplay); ?></p>
                    <p class="text-xs text-muted mt-1"><?php echo e(__('Total Tokens Used')); ?></p>
                </div>
            </div>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($byProvider->count() > 0): ?>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <div class="panel p-6">
                    <h2 class="text-lg font-bold text-ink mb-1"><?php echo e(__('Usage by Provider & Model')); ?></h2>
                    <p class="text-xs text-muted mb-5"><?php echo e(__('All-time breakdown')); ?></p>

                    <?php
                        $totalCost = $byProvider->sum('cost');
                        $providerColors = [
                            'openai' => ['color' => 'bg-success/100', 'bg' => 'bg-success/10', 'text' => 'text-success'],
                            'anthropic' => ['color' => 'bg-warning/100', 'bg' => 'bg-warning/10', 'text' => 'text-warning'],
                            'gemini' => ['color' => 'bg-info/100', 'bg' => 'bg-info/10', 'text' => 'text-info'],
                            'google' => ['color' => 'bg-info/100', 'bg' => 'bg-info/10', 'text' => 'text-info'],
                            'mistral' => ['color' => 'bg-rose-500', 'bg' => 'bg-rose-50', 'text' => 'text-rose-700'],
                        ];
                        $defaultColor = ['color' => 'bg-surface0', 'bg' => 'bg-surface', 'text' => 'text-ink/80'];
                    ?>

                    <div class="space-y-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $byProvider; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <?php
                            $pKey = strtolower($row->provider);
                            $pc = $providerColors[$pKey] ?? $defaultColor;
                            $percent = $totalCost > 0 ? round(($row->cost / $totalCost) * 100) : 0;
                        ?>
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full <?php echo e($pc['color']); ?>"></span>
                                    <span class="text-sm font-semibold text-ink/80"><?php echo e($row->provider); ?> / <?php echo e($row->model); ?></span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-bold text-ink"><?php echo \App\Helpers\CurrencyHelper::display($row->cost); ?></span>
                                    <span class="text-[10px] font-bold <?php echo e($pc['text']); ?> <?php echo e($pc['bg']); ?> px-2 py-0.5 rounded-full"><?php echo e($percent); ?>%</span>
                                </div>
                            </div>
                            <div class="w-full bg-surface rounded-full h-2.5">
                                <div class="h-2.5 rounded-full <?php echo e($pc['color']); ?> transition-all" style="width: <?php echo e(max($percent, 1)); ?>%"></div>
                            </div>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>

                    <div class="mt-5 pt-4 border-t border-border/50 flex items-center justify-between">
                        <span class="text-sm font-bold text-ink"><?php echo e(__('Total')); ?></span>
                        <span class="text-xl font-extrabold text-ink"><?php echo \App\Helpers\CurrencyHelper::display($totalCost); ?></span>
                    </div>
                </div>

                
                <div class="panel overflow-hidden">
                    <div class="px-6 py-4 border-b border-border/50 bg-surface/50">
                        <h2 class="text-lg font-bold text-ink"><?php echo e(__('Top Models Used')); ?></h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-surface border-b border-border/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-muted uppercase tracking-wider"><?php echo e(__('Model')); ?></th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-muted uppercase tracking-wider"><?php echo e(__('Requests')); ?></th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-muted uppercase tracking-wider"><?php echo e(__('Tokens')); ?></th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-muted uppercase tracking-wider"><?php echo e(__('Cost')); ?></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border/60">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $byProvider; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <?php
                                    $pKey = strtolower($row->provider);
                                    $pc = $providerColors[$pKey] ?? $defaultColor;
                                    $rowTokens = $row->tokens;
                                    $rowTokenDisplay = $rowTokens >= 1000000
                                        ? number_format($rowTokens / 1000000, 1) . 'M'
                                        : ($rowTokens >= 1000 ? number_format($rowTokens / 1000, 1) . 'K' : number_format($rowTokens));
                                ?>
                                <tr class="hover:bg-surface transition-colors">
                                    <td class="px-6 py-3">
                                        <span class="px-2 py-1 text-[10px] font-bold rounded-lg <?php echo e($pc['bg']); ?> <?php echo e($pc['text']); ?>"><?php echo e($row->model); ?></span>
                                    </td>
                                    <td class="px-6 py-3 text-muted font-medium"><?php echo e(number_format($row->requests)); ?></td>
                                    <td class="px-6 py-3 text-muted"><?php echo e($rowTokenDisplay); ?></td>
                                    <td class="px-6 py-3 text-right font-bold text-ink"><?php echo \App\Helpers\CurrencyHelper::display($row->cost); ?></td>
                                </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc8c9fd5d7827a77a31381de67195f0c3)): ?>
<?php $attributes = $__attributesOriginalc8c9fd5d7827a77a31381de67195f0c3; ?>
<?php unset($__attributesOriginalc8c9fd5d7827a77a31381de67195f0c3); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc8c9fd5d7827a77a31381de67195f0c3)): ?>
<?php $component = $__componentOriginalc8c9fd5d7827a77a31381de67195f0c3; ?>
<?php unset($__componentOriginalc8c9fd5d7827a77a31381de67195f0c3); ?>
<?php endif; ?>
<?php /**PATH /home/dahimail.com/public_html/resources/views/admin/ai-usage.blade.php ENDPATH**/ ?>
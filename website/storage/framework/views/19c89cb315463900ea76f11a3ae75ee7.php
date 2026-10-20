<?php if (isset($component)) { $__componentOriginalc8c9fd5d7827a77a31381de67195f0c3 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc8c9fd5d7827a77a31381de67195f0c3 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.admin','data' => ['title' => __('System Health'),'subtitle' => __('Monitor infrastructure, services, and performance metrics.')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.admin'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('System Health')),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Monitor infrastructure, services, and performance metrics.'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div class="space-y-6">
        
        <div class="flex items-center justify-end">
            <div class="flex items-center gap-2">
                <?php
                    $hasIssues = $failedJobs > 0 || ($diskUsage['used_percent'] !== null && $diskUsage['used_percent'] > 90);
                ?>
                <span class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium <?php echo e($hasIssues ? 'bg-warning/15 text-warning' : 'bg-success/15 text-success'); ?> rounded-full">
                    <span class="w-2 h-2 <?php echo e($hasIssues ? 'bg-warning/100' : 'bg-success/100'); ?> rounded-full animate-pulse"></span>
                    <?php echo e($hasIssues ? __('Needs Attention') : __('All Systems Operational')); ?>

                </span>
                <a href="<?php echo e(route('admin.system')); ?>" class="px-4 py-2 text-sm font-medium border border-border rounded-xl hover:bg-surface transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <?php echo e(__('Refresh')); ?>

                </a>
            </div>
        </div>

        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php
            $queueStatus = $pendingJobs > 100 ? 'critical' : ($pendingJobs > 50 ? 'warning' : 'healthy');
            $failedStatus = $failedJobs > 10 ? 'critical' : ($failedJobs > 0 ? 'warning' : 'healthy');
            $diskStatus = ($diskUsage['used_percent'] ?? 0) > 90 ? 'critical' : (($diskUsage['used_percent'] ?? 0) > 75 ? 'warning' : 'healthy');
            $dbSizeStatus = ($dbSize ?? 0) > 5000 ? 'warning' : 'healthy';

            $healthMetrics = [
                [
                    'label' => __('Queue Depth'),
                    'value' => number_format($pendingJobs),
                    'unit' => __('jobs'),
                    'status' => $queueStatus,
                    'detail' => !empty($queueDepths)
                        ? collect($queueDepths)->map(fn($c, $q) => "$q: $c")->implode(', ')
                        : __('No pending jobs'),
                    'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
                ],
                [
                    'label' => __('Failed Jobs'),
                    'value' => number_format($failedJobs),
                    'unit' => __('jobs'),
                    'status' => $failedStatus,
                    'detail' => $failedJobs > 0 && count($recentFailedJobs) > 0
                        ? __('Latest:') . ' ' . ($recentFailedJobs[0]->job_class ?? __('Unknown'))
                        : __('No recent failures'),
                    'icon' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                ],
                [
                    'label' => __('Database Size'),
                    'value' => $dbSize !== null ? $dbSize : __('N/A'),
                    'unit' => $dbSize !== null ? 'MB' : '',
                    'status' => $dbSizeStatus,
                    'detail' => $dbSize !== null ? __('MySQL database size on disk') : __('Could not read database size'),
                    'icon' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4',
                ],
                [
                    'label' => __('Disk Usage'),
                    'value' => $diskUsage['used_percent'] !== null ? $diskUsage['used_percent'] : __('N/A'),
                    'unit' => $diskUsage['used_percent'] !== null ? '%' : '',
                    'status' => $diskStatus,
                    'detail' => $diskUsage['total_gb'] !== null
                        ? number_format($diskUsage['total_gb'] - $diskUsage['free_gb'], 1) . ' ' . __('GB used') . ' / ' . number_format($diskUsage['total_gb'], 1) . ' ' . __('GB total')
                        : __('Could not read disk info'),
                    'icon' => 'M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01',
                ],
                [
                    'label' => __('Log Errors'),
                    'value' => count($recentErrors),
                    'unit' => __('recent'),
                    'status' => count($recentErrors) > 10 ? 'critical' : (count($recentErrors) > 0 ? 'warning' : 'healthy'),
                    'detail' => count($recentErrors) > 0
                        ? __('Found in last 5KB of log file')
                        : __('No recent errors in log'),
                    'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                ],
                [
                    'label' => __('Environment'),
                    'value' => ucfirst($appInfo['environment']),
                    'unit' => '',
                    'status' => $appInfo['debug_mode'] && $appInfo['environment'] === 'production' ? 'critical' : 'healthy',
                    'detail' => __('Debug:') . ' ' . ($appInfo['debug_mode'] ? __('ON') : __('OFF')) . ' | ' . __('Cache:') . ' ' . $appInfo['cache_driver'],
                    'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
                ],
            ];
            ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $healthMetrics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
            <?php
                $statusColors = [
                    'healthy' => ['bg' => 'bg-success/10', 'border' => 'border-success/20', 'badge' => 'bg-success/15 text-success', 'dot' => 'bg-success/100'],
                    'warning' => ['bg' => 'bg-warning/10', 'border' => 'border-warning/20', 'badge' => 'bg-warning/15 text-warning', 'dot' => 'bg-warning/100'],
                    'critical' => ['bg' => 'bg-danger/10', 'border' => 'border-danger/20', 'badge' => 'bg-danger/15 text-danger', 'dot' => 'bg-danger/100'],
                ];
                $sc = $statusColors[$metric['status']];
            ?>
            <div class="bg-surface-2 rounded-2xl border border-border p-5 hover:shadow-sm transition-shadow">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 <?php echo e($sc['bg']); ?> rounded-xl flex items-center justify-center border <?php echo e($sc['border']); ?>">
                            <svg class="w-5 h-5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($metric['icon']); ?>"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-muted"><?php echo e($metric['label']); ?></span>
                    </div>
                    <span class="flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-full <?php echo e($sc['badge']); ?>">
                        <span class="w-1.5 h-1.5 rounded-full <?php echo e($sc['dot']); ?>"></span>
                        <?php echo e(ucfirst($metric['status'])); ?>

                    </span>
                </div>
                <div class="mt-2">
                    <span class="text-3xl font-bold text-ink"><?php echo e($metric['value']); ?></span>
                    <span class="text-sm text-muted ml-1"><?php echo e($metric['unit']); ?></span>
                </div>
                <p class="text-xs text-muted mt-2"><?php echo e($metric['detail']); ?></p>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>

        
        <div class="bg-surface-2 rounded-2xl border border-border p-6">
            <h2 class="text-lg font-semibold text-ink mb-4"><?php echo e(__('Database Table Counts')); ?></h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $tableCounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <div class="flex items-center justify-between p-3 bg-surface rounded-xl">
                    <span class="text-sm font-medium text-muted"><?php echo e($table); ?></span>
                    <span class="text-sm font-bold text-ink"><?php echo e(is_numeric($count) ? number_format($count) : $count); ?></span>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>

        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-surface-2 rounded-2xl border border-border p-6">
                <h2 class="text-lg font-semibold text-ink mb-4"><?php echo e(__('Application Information')); ?></h2>
                <div class="space-y-3">
                    <?php
                    $serverInfo = [
                        ['key' => __('PHP Version'), 'value' => $appInfo['php_version']],
                        ['key' => __('Laravel Version'), 'value' => $appInfo['laravel_version']],
                        ['key' => __('Environment'), 'value' => ucfirst($appInfo['environment'])],
                        ['key' => __('Debug Mode'), 'value' => $appInfo['debug_mode'] ? __('Enabled') : __('Disabled')],
                        ['key' => __('Cache Driver'), 'value' => $appInfo['cache_driver']],
                        ['key' => __('Queue Driver'), 'value' => $appInfo['queue_driver']],
                        ['key' => __('Session Driver'), 'value' => $appInfo['session_driver']],
                    ];
                    ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $serverInfo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                    <div class="flex items-center justify-between py-2 border-b border-border last:border-0">
                        <span class="text-sm text-muted"><?php echo e($info['key']); ?></span>
                        <span class="text-sm font-mono font-medium text-ink"><?php echo e($info['value']); ?></span>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </div>

            <div class="bg-surface-2 rounded-2xl border border-border p-6">
                <h2 class="text-lg font-semibold text-ink mb-4"><?php echo e(__('Storage Usage')); ?></h2>
                <div class="space-y-4">
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($diskUsage['used_percent'] !== null): ?>
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-sm font-medium text-muted"><?php echo e(__('Disk Usage')); ?></span>
                            <span class="text-sm font-semibold text-ink"><?php echo e($diskUsage['used_percent']); ?>%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all <?php echo e($diskUsage['used_percent'] > 90 ? 'bg-danger/100' : ($diskUsage['used_percent'] > 75 ? 'bg-warning/100' : 'bg-success/100')); ?>"
                                 style="width: <?php echo e($diskUsage['used_percent']); ?>%"></div>
                        </div>
                        <p class="text-xs text-muted mt-1"><?php echo e(number_format($diskUsage['free_gb'], 1)); ?> <?php echo e(__('GB free of')); ?> <?php echo e(number_format($diskUsage['total_gb'], 1)); ?> GB</p>
                    </div>
                    <?php else: ?>
                    <p class="text-sm text-muted"><?php echo e(__('Disk usage info unavailable.')); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($storageSizes)): ?>
                    <div class="pt-2 border-t border-border">
                        <p class="text-xs font-bold text-muted uppercase tracking-wider mb-2"><?php echo e(__('Storage Directories')); ?></p>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $storageSizes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dir => $sizeMb): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <div class="flex items-center justify-between py-1.5">
                            <span class="text-sm text-muted font-mono">storage/<?php echo e($dir); ?></span>
                            <span class="text-sm font-semibold text-ink"><?php echo e($sizeMb); ?> MB</span>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($recentFailedJobs) > 0): ?>
        <div class="bg-surface-2 rounded-2xl border border-border overflow-hidden">
            <div class="px-6 py-4 border-b border-border bg-danger/10/50 dark:bg-red-950/20">
                <h2 class="text-lg font-semibold text-ink"><?php echo e(__('Recent Failed Jobs')); ?></h2>
                <p class="text-xs text-muted mt-0.5"><?php echo e(__('Last')); ?> <?php echo e(count($recentFailedJobs)); ?> <?php echo e(__('failed jobs')); ?></p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-surface border-b border-border">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-muted uppercase tracking-wider"><?php echo e(__('Job')); ?></th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-muted uppercase tracking-wider"><?php echo e(__('Queue')); ?></th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-muted uppercase tracking-wider"><?php echo e(__('Error')); ?></th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-muted uppercase tracking-wider"><?php echo e(__('Failed At')); ?></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $recentFailedJobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                        <tr class="hover:bg-surface transition-colors">
                            <td class="px-4 py-2.5 text-xs font-mono text-muted"><?php echo e($job->job_class); ?></td>
                            <td class="px-4 py-2.5 text-xs text-muted"><?php echo e($job->queue); ?></td>
                            <td class="px-4 py-2.5 text-xs text-danger dark:text-red-400 max-w-xs truncate" title="<?php echo e($job->exception_summary); ?>"><?php echo e(Str::limit($job->exception_summary, 100)); ?></td>
                            <td class="px-4 py-2.5 text-xs text-muted"><?php echo e($job->failed_at); ?></td>
                        </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($recentErrors) > 0): ?>
        <div class="bg-surface-2 rounded-2xl border border-border overflow-hidden">
            <div class="px-6 py-4 border-b border-border bg-warning/10/50 dark:bg-yellow-950/20">
                <h2 class="text-lg font-semibold text-ink"><?php echo e(__('Recent Log Errors')); ?></h2>
                <p class="text-xs text-muted mt-0.5"><?php echo e(__('Extracted from laravel.log (last 5KB)')); ?></p>
            </div>
            <div class="p-4 space-y-2 max-h-96 overflow-y-auto">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $recentErrors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <div class="p-3 bg-danger/10 border border-red-100 rounded-lg">
                    <code class="text-[11px] text-danger break-all leading-relaxed"><?php echo e(Str::limit($error, 300)); ?></code>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>
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
<?php /**PATH /home/dahimail.com/public_html/resources/views/admin/system.blade.php ENDPATH**/ ?>
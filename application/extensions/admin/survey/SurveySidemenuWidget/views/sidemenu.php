<?php $this->render('sideMenuLogo'); ?>
<div class="sidebar-container">
<div id="left-sidebar" class="fade sidebar sidebar-left show">
    <div class="d-flex">
        <div class="sidebar-icons-container">
            <?php
            // Define all sidebar icons
            $sidebarIcons = [
                // Survey structure
                [
                    'onClick' => "window.location='" . App()->createUrl(
                            'editorLink/index',
                            ['route' => 'survey/' . $sid]
                        ) . "'",
                    'tooltip' => gT('Survey structure'),
                    'iconClass' => 'ri-bar-chart-horizontal-line'
                ],
                // Survey settings
                [
                    'dataTarget' => '#survey-settings-panel',
                    'onClick' => "window.location='" . App()->createUrl(
                            'editorLink/index',
                            [
                                'route' => 'survey/' . $sid
                                    . '/settings/generalsettings'
                            ]
                        ) . "'",
                    'tooltip' => gT('Survey settings'),
                    'activePanel' => $this->activePanel == 'survey-settings-panel',
                    'iconClass' => 'ri-settings-3-fill'
                ],
                // Survey presentation
                [
                    'dataTarget' => '#survey-presentation-panel',
                    'onClick' => "window.location='" . App()->createUrl(
                            'editorLink/index',
                            [
                                'route' => 'survey/' . $sid
                                    . '/presentation/theme_options'
                            ]
                        ) . "'",
                    'tooltip' => gT('Survey presentation'),
                    'activePanel' => $this->activePanel == 'survey-presentation-panel',
                    'iconClass' => 'ri-paint-fill'
                ],
                // Survey menu
                [
                    'dataTarget' => '#survey-menu-panel',
                    'onClick' => "window.location='" . App()->createUrl(
                            'admin/tokens/sa/index/surveyid/'
                            . $sid
                        ) . "'",
                    'tooltip' => gT('Survey menu'),
                    'activePanel' => $this->activePanel == 'survey-menu-panel',
                    'iconClass' => 'ri-file-text-line'
                ],
                // Survey permissions
                [
                    'dataTarget' => '#survey-permissions-panel',
                    'onClick' => "window.location='" . App()->createUrl(
                            'surveyPermissions/index/',
                            ['surveyid' => $sid]
                        ) . "'",
                    'tooltip' => gT('Survey permissions'),
                    'activePanel' => $this->activePanel == 'survey-permissions-panel',
                    'iconClass' => 'ri-group-line'
                ],
                // Quick translation
                [
                    'dataTarget' => '#survey-quick-translation',
                    'disabled' => count($this->allLanguages) <= 1,
                    'onClick' => "window.location='" . App()->createUrl(
                            'quickTranslation/index/',
                            ['surveyid' => $sid]
                        ) . "'",
                    'tooltip' => gT('Quick translation'),
                    'tooltipDisabled' => gT('Quick translation') . ': '
                        . gT(
                            'Currently there are no additional languages configured for this survey.'
                        ),
                    'activePanel' => $this->activePanel == 'survey-quick-translation',
                    'iconClass' => 'ri-translate-2'
                ]
            ];

            // Render all sidebar icons
            foreach ($sidebarIcons as $iconData) {
                $this->render('sidebarIconsItem', $iconData);
            }
            ?>

        </div>
        <div class="panels">
            <div id="survey-settings-panel" class="side-panel d-flex <?php echo $this->activePanel !== 'survey-settings-panel' ? 'd-none' : ''?>">
                <div class="survey-structure">
                    <div class="survey-settings">
                        <div class="d-flex sidebar-header align-items-center justify-content-between right-side-bar-header primary">
                            <?php echo gT('Survey Settings'); ?>
                            <button type="button" id="btn-close-survey-settings-panel" class="p-0 btn-close-lime btn btn-link">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-black fill-current">
                                    <g clip-path="url(#clip0_1_4259)">
                                        <path d="M12.0007 10.586L16.9507 5.63599L18.3647 7.04999L13.4147 12L18.3647 16.95L16.9507 18.364L12.0007 13.414L7.05072 18.364L5.63672 16.95L10.5867 12L5.63672 7.04999L7.05072 5.63599L12.0007 10.586Z"></path>
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_1_4259">
                                            <rect width="20" height="20" fill="white"></rect>
                                        </clipPath>
                                    </defs>
                                </svg>
                            </button>
                        </div>

                        <?php foreach ($sideMenu['settings'] as $item) : ?>
                            <a href="<?php echo $item['url']; ?>">
                                <div class="survey-settings-panel-item text-black <?php echo (isset($item['selected']) && $item['selected']) ? ' bg-primary' : ''; ?>">
                                    <label class=" cursor-pointer mb-0 form-label <?php echo (isset($item['selected']) && $item['selected']) ? ' text-white' : ' text-black'; ?>">
                                        <?php echo $item['name']; ?>
                                    </label>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div id="survey-menu-panel" class="side-panel d-flex <?php echo $this->activePanel !== 'survey-menu-panel' ? 'd-none' : ''?>">
                <div class="survey-structure">
                    <div class="survey-settings">
                        <div class="d-flex sidebar-header align-items-center justify-content-between right-side-bar-header primary">
                            <?php echo gT('Survey menu'); ?>
                            <button type="button" id="btn-close-survey-settings-panel" class="p-0 btn-close-lime btn btn-link">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-black fill-current">
                                    <g clip-path="url(#clip0_1_4259)">
                                        <path d="M12.0007 10.586L16.9507 5.63599L18.3647 7.04999L13.4147 12L18.3647 16.95L16.9507 18.364L12.0007 13.414L7.05072 18.364L5.63672 16.95L10.5867 12L5.63672 7.04999L7.05072 5.63599L12.0007 10.586Z"></path>
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_1_4259">
                                            <rect width="20" height="20" fill="white"></rect>
                                        </clipPath>
                                    </defs>
                                </svg>
                            </button>
                        </div>
                        <?php foreach ($sideMenu['menu'] as $item) : ?>
                            <a href="<?php echo $item['disabled'] ?? false ? '#' : $item['url']; ?>" class="<?php echo $item['disabled'] ?? false ? 'disabled' : ''; ?>">
                                <div class="survey-settings-panel-item text-black <?php echo (isset($item['selected']) && $item['selected']) ? ' bg-primary' : ''; ?>">
                                    <label class=" cursor-pointer mb-0 form-label <?php echo (isset($item['selected']) && $item['selected'])? ' text-white' : ' text-black'; ?>">
                                        <?php echo $item['name']; ?>
                                    </label>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div id="survey-presentation-panel" class="side-panel d-flex <?php echo $this->activePanel !== 'survey-presentation-panel' ? 'd-none' : ''?>">
                <div class="survey-structure">
                    <div class="survey-settings">
                        <div class="d-flex sidebar-header align-items-center justify-content-between right-side-bar-header primary">
                            <?php echo gT('Survey presentation'); ?>
                            <button type="button" id="btn-close-survey-settings-panel" class="p-0 btn-close-lime btn btn-link">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-black fill-current">
                                    <g clip-path="url(#clip0_1_4259)">
                                        <path d="M12.0007 10.586L16.9507 5.63599L18.3647 7.04999L13.4147 12L18.3647 16.95L16.9507 18.364L12.0007 13.414L7.05072 18.364L5.63672 16.95L10.5867 12L5.63672 7.04999L7.05072 5.63599L12.0007 10.586Z"></path>
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_1_4259">
                                            <rect width="20" height="20" fill="white"></rect>
                                        </clipPath>
                                    </defs>
                                </svg>
                            </button>
                        </div>
                        <?php foreach ($sideMenu['presentation'] as $item) : ?>
                            <a href="<?php echo $item['url']; ?>">
                                <div class="survey-settings-panel-item text-black <?php echo (isset($item['selected']) && $item['selected'])? ' bg-primary' : ''; ?>">
                                    <label class=" cursor-pointer mb-0 form-label <?php echo (isset($item['selected']) && $item['selected']) ? ' text-white' : ' text-black'; ?>">
                                        <?php echo $item['name']; ?>
                                    </label>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div id="survey-permissions-panel" class="side-panel d-flex <?php echo $this->activePanel !== 'survey-permissions-panel' ? 'd-none' : ''?>">
                <div class="survey-structure">
                    <div class="survey-settings">
                        <div class="d-flex sidebar-header align-items-center justify-content-between right-side-bar-header primary">
                            <?php echo gT('Survey permissions'); ?>
                            <button type="button" id="btn-close-survey-settings-panel" class="p-0 btn-close-lime btn btn-link">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-black fill-current">
                                    <g clip-path="url(#clip0_1_4259)">
                                        <path d="M12.0007 10.586L16.9507 5.63599L18.3647 7.04999L13.4147 12L18.3647 16.95L16.9507 18.364L12.0007 13.414L7.05072 18.364L5.63672 16.95L10.5867 12L5.63672 7.04999L7.05072 5.63599L12.0007 10.586Z"></path>
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_1_4259">
                                            <rect width="20" height="20" fill="white"></rect>
                                        </clipPath>
                                    </defs>
                                </svg>
                            </button>
                        </div>
                        <?php
                            $currentPage = App()->request->requestUri;
                            $url = App()->createUrl('surveyPermissions/index', ['surveyid' => $sid]);
                        ?>
                        <a href="<?php echo $url;?>">
                            <div class="survey-settings-panel-item text-black <?php echo str_contains($url, $currentPage) ? ' bg-primary' : ''; ?>">
                                <label class=" cursor-pointer mb-0 form-label <?php echo str_contains($url, $currentPage) ? ' text-white' : ' text-black'; ?>">
                                    <?php echo gT('Permissions'); ?>
                                </label>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <?php if (count($this->allLanguages) > 1) : ?>
                <div id="survey-quick-translation" class="side-panel d-flex <?php echo $this->activePanel !== 'survey-quick-translation' ? 'd-none' : ''?>">
                    <div class="survey-structure">
                        <div class="survey-settings">
                            <div class="d-flex sidebar-header align-items-center justify-content-between right-side-bar-header primary">
                                <?php echo gT('Quick translation'); ?>
                                <button type="button" id="btn-close-survey-settings-panel" class="p-0 btn-close-lime btn btn-link">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-black fill-current">
                                        <g clip-path="url(#clip0_1_4259)">
                                            <path d="M12.0007 10.586L16.9507 5.63599L18.3647 7.04999L13.4147 12L18.3647 16.95L16.9507 18.364L12.0007 13.414L7.05072 18.364L5.63672 16.95L10.5867 12L5.63672 7.04999L7.05072 5.63599L12.0007 10.586Z"></path>
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_1_4259">
                                                <rect width="20" height="20" fill="white"></rect>
                                            </clipPath>
                                        </defs>
                                    </svg>
                                </button>
                            </div>

                            <?php
                                $currentPage = App()->request->requestUri;
                                $url = App()->createUrl('quickTranslation/index', ['surveyid' => $sid]);
                            ?>
                            <a href="<?php echo $url; ?>">
                                <div class="survey-settings-panel-item text-black <?php echo str_contains($url, $currentPage) ? ' bg-primary' : ''; ?>">
                                    <label class=" cursor-pointer mb-0 form-label <?php echo str_contains($url, $currentPage) ? ' text-white' : ' text-black'; ?>">
                                        <?php echo gT('Quick translation'); ?>
                                    </label>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>

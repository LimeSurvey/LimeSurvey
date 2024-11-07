<?php $this->render('sideMenuLogo'); ?>
<div style="width: 350px;">
<div id="left-sidebar" class="fade sidebar sidebar-left show">
    <div class="d-flex" style="height: 100%;">
        <div class="sidebar-icons" style="width: 52px;">
            <div class="sidebar-icons-item">
                <div class="sidebar-icon" onclick="window.location='<?php echo App()->createUrl('editorLink/index', ['route' => 'survey/' . $sid]);?>'">
                    <div data-bs-toggle="tooltip"
                         title="<?php echo gT('Survey structure'); ?>"
                         data-bs-offset="0, 20"
                         data-bs-placement="right">
                        <i class="ri-bar-chart-horizontal-line btn btn-g-800 btn-icon"></i>
                    </div>
                </div>
            </div>
            <div class="sidebar-icons-item">
                <div class="sidebar-icon" data-target="#survey-settings-panel" onclick="window.location='<?php echo App()->createUrl('surveyAdministration/view/', ['surveyid' => $sid]);?>'">
                    <div data-bs-toggle="tooltip"
                         title="<?php echo gT('Survey settings'); ?>"
                         data-bs-offset="0, 20"
                         data-bs-placement="right">
                        <i class="ri-settings-3-fill btn btn-g-800 btn-icon <?php echo $this->activePanel == 'survey-settings-panel' ? 'active' : ''?>"></i>
                    </div>
                </div>
            </div>
            <div class="sidebar-icons-item">
                <div class="sidebar-icon" data-target="#survey-menu-panel" onclick="window.location='<?php echo App()->createUrl('questionAdministration/listQuestions/', ['surveyid' => $sid]);?>'">
                    <div data-bs-toggle="tooltip"
                         title="<?php echo gT('Survey menu'); ?>"
                         data-bs-offset="0, 20"
                         data-bs-placement="right">
                        <i class="ri-file-text-line btn btn-g-800 btn-icon <?php echo $this->activePanel == 'survey-menu-panel' ? 'active' : ''?>"></i>
                    </div>
                </div>
            </div>
            <div class="sidebar-icons-item">
                <div class="sidebar-icon" data-target="#survey-presentation-panel" onclick="window.location='<?php echo App()->createUrl('editorLink/index', ['route' => 'survey/' .  $sid . '/presentation/presentation']);?>'">
                    <div data-bs-toggle="tooltip"
                         title="<?php echo gT('Survey presentation'); ?>"
                         data-bs-offset="0, 20"
                         data-bs-placement="right">
                        <i class="ri-paint-fill btn btn-g-800 btn-icon <?php echo $this->activePanel == 'survey-presentation-panel' ? 'active' : ''?>"></i>
                    </div>
                </div>
            </div>
            <div class="sidebar-icons-item">
                <div class="sidebar-icon" data-target="#survey-permissions-panel" onclick="window.location='<?php echo App()->createUrl('surveyPermissions/index/', ['surveyid' => $sid]);?>'">
                    <div data-bs-toggle="tooltip"
                         title="<?php echo gT('Survey permissions'); ?>"
                         data-bs-offset="0, 20"
                         data-bs-placement="right">
                        <i class="ri-lock-2-line btn btn-g-800 btn-icon <?php echo $this->activePanel == 'survey-permissions-panel' ? 'active' : ''?>"></i>
                    </div>
                </div>
            </div>
            <?php if (count($this->allLanguages) > 1) : ?>
                <div class="sidebar-icons-item">
                    <div class="sidebar-icon" data-target="#survey-quick-translation" onclick="window.location='<?php echo App()->createUrl('quickTranslation/index/', ['surveyid' => $sid]);?>'">
                        <div style="z-index: 5000;"
                             data-bs-toggle="tooltip"
                             title="<?php echo gT('Quick Translations'); ?>"
                             data-bs-offset="0, 20"
                             data-bs-placement="right">
                            <i class="ri-translate-2 btn btn-g-800 btn-icon <?php echo $this->activePanel == 'survey-quick-translation' ? 'active' : ''?>"></i>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div>
            <div id="survey-settings-panel" class="side-panel d-flex <?php echo $this->activePanel !== 'survey-settings-panel' ? 'd-none' : ''?>" style="height: 100%;">
                <div class="survey-structure px-2" style="overflow-y: auto; width: 290px;">
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

                        <?php $currentPage = App()->request->getPathInfo() . '?' . App()->request->getQueryString();?>
                        <?php foreach ($sideMenu['settings'] as $item) : ?>
                            <a href="<?php echo $item['url']; ?>">
                                <div class="px-4 py-3 d-flex align-items-center cursor-pointer rounded text-black <?php echo (isset($item['selected']) && $item['selected']) ? ' bg-primary' : ''; ?>">
                                    <label class=" cursor-pointer mb-0 form-label <?php echo (isset($item['selected']) && $item['selected']) ? ' text-white' : ' text-black'; ?>">
                                        <?php echo $item['name']; ?>
                                    </label>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div id="survey-menu-panel" class="side-panel d-flex <?php echo $this->activePanel !== 'survey-menu-panel' ? 'd-none' : ''?>" style="height: 100%;">
                <div class="survey-structure px-2" style="overflow-y: auto; width: 290px;">
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
                                <div class="px-4 py-3 d-flex align-items-center cursor-pointer rounded text-black <?php echo (isset($item['selected']) && $item['selected']) ? ' bg-primary' : ''; ?>">
                                    <label class=" cursor-pointer mb-0 form-label <?php echo (isset($item['selected']) && $item['selected'])? ' text-white' : ' text-black'; ?>">
                                        <?php echo $item['name']; ?>
                                    </label>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div id="survey-presentation-panel" class="side-panel d-flex <?php echo $this->activePanel !== 'survey-presentation-panel' ? 'd-none' : ''?>" style="height: 100%;">
                <div class="survey-structure px-2" style="overflow-y: auto; width: 290px;">
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
                                <div class="px-4 py-3 d-flex align-items-center cursor-pointer rounded text-black <?php echo (isset($item['selected']) && $item['selected'])? ' bg-primary' : ''; ?>">
                                    <label class=" cursor-pointer mb-0 form-label <?php echo (isset($item['selected']) && $item['selected']) ? ' text-white' : ' text-black'; ?>">
                                        <?php echo $item['name']; ?>
                                    </label>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div id="survey-permissions-panel" class="side-panel d-flex <?php echo $this->activePanel !== 'survey-permissions-panel' ? 'd-none' : ''?>" style="height: 100%;">
                <div class="survey-structure px-2" style="overflow-y: auto; width: 290px;">
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
                            <div class="px-4 py-3 d-flex align-items-center cursor-pointer rounded text-black <?php echo str_contains($url, $currentPage) ? ' bg-primary' : ''; ?>">
                                <label class=" cursor-pointer mb-0 form-label <?php echo str_contains($url, $currentPage) ? ' text-white' : ' text-black'; ?>">
                                    <?php echo gT('Permissions'); ?>
                                </label>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            <?php if (count($this->allLanguages) > 1) : ?>
                <div id="survey-quick-translation" class="side-panel d-flex <?php echo $this->activePanel !== 'survey-quick-translation' ? 'd-none' : ''?>" style="height: 100%;">
                    <div class="survey-structure px-2" style="overflow-y: auto; width: 290px;">
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
                                <div class="px-4 py-3 d-flex align-items-center cursor-pointer rounded text-black <?php echo str_contains($url, $currentPage) ? ' bg-primary' : ''; ?>">
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

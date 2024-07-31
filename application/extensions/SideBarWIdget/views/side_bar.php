<?php
/**
 * Renders the main view of the SideBarWidget
 */

?>
<div class="sidebar p-2 sidebar-left">
    <div class="d-flex" style="height: 100%">
        <div style="width: 52px">
            <?php foreach ($sideBarItems as $sideBarItem) : ?>
                <div class="cursor-pointer d-flex justify-content-center">
                    <div data-bs-toggle="tooltip"
                         title="<?= $tooltip ?>"
                         data-bs-offset="0, 20"
                         data-bs-placement="right">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="36"
                            height="36"
                            fill="none"
                            class="text-black"
                        >
                            <g clipPath="url(#a)">
                                <rect width="36" height="36" fill=bg-white rx="4"/>
                                <g clipPath="url(#b)">
                                    <path
                                        d="M18 10.5v1.667h-7.5V10.5H18Zm3.333 13.333V25.5H10.5v-1.667h10.833Zm5-6.666v1.666H10.5v-1.666h15.833Z"/>
                                </g>
                            </g>
                            <defs>
                                <clipPath id="a">
                                    <rect width="36" height="36" rx="4"/>
                                </clipPath>
                                <clipPath id="b">
                                    <path d="M8 8h20v20H8z"/>
                                </clipPath>
                            </defs>
                        </svg>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

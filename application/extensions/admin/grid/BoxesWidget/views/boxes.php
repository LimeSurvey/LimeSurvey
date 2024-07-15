<div class="container-fluid box-widget">
    <div class="row row-cols-<?php echo $boxesbyrow ?>">
    <?php foreach ($items as $item) : ?>
        <div class="col">
            <?php if ($item['type'] == 0) : ?>
                <div class="card card-primary "
                     data-url="<?php echo $item['link']?>"
                    <?php if ($item['external']) : ?> data-target="_blank" <?php endif; ?>
                >
                    <div class="card-header">
                        <div class="card-title">
                            <?php echo viewHelper::filterScript(gT($item['survey']->defaultlanguage->surveyls_title)); ?>
                        </div>
                        <span class="card-detail"><?php echo $item['survey']->creationdate?></span>
                    </div>
                    <div class="card-footer">
                        <div class="content">
                            <div>
                                <?php echo $item['survey']->countFullAnswers == 0 ? 'No' : $item['survey']->countFullAnswers?> responses
                            </div>
                            <div class="icons">
                                <i class="<?php echo $item['icon']?>" class="survey-state" data-bs-toggle="tooltip" title="<?php echo $item['iconAlter']?>"></i>
<!--                                <i class="ri-more-fill dropdown"></i>-->
                                <?php echo $item['survey']->getButtons(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif ($item['type'] == 2) : ?>
                <div class="card card-primary card-clickable card-link"
                     data-url="<?php echo $item['link']?>"
                    <?php if ($item['color']) : ?> style="color:<?php echo $item['color']?>;border-color:<?php echo $item['color']?>" <?php endif; ?>
                    <?php if ($item['external']) : ?> data-target="_blank" <?php endif; ?>
                >
                    <div class="card-body">
                        <i class="<?php echo $item['icon']?>"></i>
                        <?php echo $item['text']?>
                    </div>

                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    </div>
    <a href="#" >Load more</a>
</div>

<style>
    .box-widget {
        padding:  0  5% !important;
    }
    .box-widget .card {
        vertical-align: middle;
    }
    .box-widget .card {
        position: relative;
        aspect-ratio: 4/3;
        padding: 15px 20px !important;
        margin:  10px 0  !important;
        border-radius: 4px;
    }
    .box-widget .card.card-clickable,
    .box-widget .card .card-header:hover {
        cursor: pointer;
    }

    .box-widget .card .card-header .card-title {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        text-overflow: ellipsis;
        white-space: initial;
        overflow: hidden;
        max-height: 100%;
        font-family: Sans-Serif;
        font-size: 1.4em;
        font-weight: 200;
        line-height: 1.3;
    }
    .box-widget .card .card-header span.card-detail  {
        margin-top: 5px;
        color: gray;
        font-size: 1.2em;
        font-weight: 400;
    }

    .box-widget .card .card-footer {
        position: absolute;
        bottom: 20px;
        left: 0;
        width: 100%;
        height: 30px;
        padding: 0;
        color: gray;
        font-size: 1.1em;
    }

    .box-widget .card .card-footer .content {
        width: 100%;
        padding-left: 20px;
    }
    .box-widget .card .card-footer .content div{
        display: inline-block;
        width: 45%;
    }

    .box-widget .card .card-footer .icons {
        font-size: 2em;
        text-align: right;
    }

    .box-widget .card .card-footer .icons a {
        outline: none;
    }

    .box-widget .card-link.card {
        border: solid #6D748C 3px;
        color: #6D748C;
    }
    .box-widget .card-link.card .card-body {
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 1.4em;
    }
    .box-widget .card-link.card .card-body i {
        padding-right: 5px;
    }

    @media (max-width: 1400px) {
        .box-widget .card .card-header .card-title {
            -webkit-line-clamp: 3;
        }
        .box-widget .row .col {
            width: 33%;
        }
    }


    @media (max-width: 1000px) {
        .box-widget .row .col {
            width: 50%;
        }
    }

    @media screen and (max-width: 767px) {
        .box-widget .row .col {
            width: 75%;
            aspect-ratio: 1/1;
            margin: 0 auto;
            padding: 0;
        }
        .dropdown {
            display: none;
        }
    }
    .box-widget .card .card-footer .content .dropdown.ls-action_dropdown {
        margin: 0;
        padding: 0;
        width: 32px;
    }

    .box-widget .card .card-footer .content .dropdown.ls-action_dropdown button {
        box-shadow: none;
        border: solid 1px white;
    }
    .box-widget .card .card-footer .content .dropdown.ls-action_dropdown button:hover {
        border: solid 1px grey;
    }

    .box-widget .card .card-footer .content .dropdown.ls-action_dropdown button i:before {
        font-size: 1.2em;
    }

</style>
<script>
    $(".card-header").click(function(event){
        console.log($(this).parent().attr("data-url"))
        if ($(this).parent().attr("data-url")) {
            window.location.href = $(this).parent().attr("data-url");
        }
    })

</script>

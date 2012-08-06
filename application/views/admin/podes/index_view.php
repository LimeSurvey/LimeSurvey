<form method='post' name='formbuilder' action='<?php echo Yii::app()->getController()->createUrl("admin/podes/index"); ?>'>
    <div class='header ui-widget-header header_statistics'>
        <div style='float:right;'><img src='<?php echo $sImageURL; ?>/maximize.png' id='showlfilter' alt='<?php $clang->eT("Maximize"); ?>'/><img src='<?php echo $sImageURL; ?>/minimize.png' id='hidelfilter' alt='<?php $clang->eT("Minimize"); ?>'/></div>
        <?php $clang->eT("Village Resources Viewer"); ?>
    </div>
    
    <!-- AUTOSCROLLING DIV CONTAINING GENERAL FILTERS -->
    <div id='podeslocationfilters' class='statisticsfilters'>
        <div id='statistics_general_filter'>
            <?php
                $error = '';
                if (!function_exists("gd_info")) {
                    $error .= '<br />'.$clang->gT('You do not have the GD Library installed. Showing charts requires the GD library to function properly.');
                    $error .= '<br />'.$clang->gT('visit http://us2.php.net/manual/en/ref.image.php for more information').'<br />';
                }
                else if (!function_exists("imageftbbox")) {
                    $error .= '<br />'.$clang->gT('You do not have the Freetype Library installed. Showing charts requires the Freetype library to function properly.');
                    $error .= '<br />'.$clang->gT('visit http://us2.php.net/manual/en/ref.image.php for more information').'<br />';
                }
            ?>
            <fieldset style='clear:both;'>
                <legend><?php $clang->eT("Location Filter"); ?></legend>
                <ul>
                    <li>
                        <label for='PotensiForm_provinsiid'><?php $clang->eT("Province :"); ?> </label>
                        <select name='PotensiForm[provinsiid]' id='PotensiForm_provinsiid'>                       
                            <option value="" selected="selected"></option>
                            <option value="11">NANGGROE ACEH DARUSSALAM</option>
                            <option value="12">SUMATERA UTARA</option>
                            <option value="13">SUMATERA BARAT</option>
                            <option value="14">RIAU</option>
                            <option value="15">JAMBI</option>
                            <option value="16">SUMATERA SELATAN</option>
                            <option value="17">BENGKULU</option>
                            <option value="18">LAMPUNG</option>
                            <option value="19">KEPULAUAN BANGKA BELITUNG</option>
                            <option value="21">KEPULAUAN RIAU</option>
                            <option value="31">DKI JAKARTA</option>
                            <option value="32">JAWA BARAT</option>
                            <option value="33">JAWA TENGAH</option>
                            <option value="34">DI YOGYAKARTA</option>
                            <option value="35">JAWA TIMUR</option>
                            <option value="36">BANTEN</option>
                            <option value="51">BALI</option>
                            <option value="52">NUSA TENGGARA BARAT</option>
                            <option value="53">NUSA TENGGARA TIMUR</option>
                            <option value="61">KALIMANTAN BARAT</option>
                            <option value="62">KALIMANTAN TENGAH</option>
                            <option value="63">KALIMANTAN SELATAN</option>
                            <option value="64">KALIMANTAN TIMUR</option>
                            <option value="71">SULAWESI UTARA</option>
                            <option value="72">SULAWESI TENGAH</option>
                            <option value="73">SULAWESI SELATAN</option>
                            <option value="74">SULAWESI TENGGARA</option>
                            <option value="75">GORONTALO</option>
                            <option value="76">SULAWESI BARAT</option>
                            <option value="81">MALUKU</option>
                            <option value="82">MALUKU UTARA</option>
                            <option value="91">PAPUA BARAT</option>
                            <option value="94">PAPUA</option>
                        </select>
                    </li>
                    <li>
                        <label for='PotensiForm_kabupatenid'><?php $clang->eT("District :"); ?> </label>
                        <select name='PotensiForm[kabupatenid]' id='PotensiForm_kabupatenid'>
                            <option value=''><?php $clang->eT("Choose District"); ?></option>
                        </select>
                    </li>
                    <li>
                        <label for='PotensiForm_kecamatanid'><?php $clang->eT("Sub-District :"); ?> </label>
                        <select name='PotensiForm[kecamatanid]' id='PotensiForm_kecamatanid'>
                            <option value='all' ><?php $clang->eT("Choose Sub-District"); ?></option>
                        </select>
                    </li>
                    <li>
                        <label for='PotensiForm_desaid'><?php $clang->eT("Village :"); ?> </label>
                        <select name='PotensiForm[desaid]' id='PotensiForm_desaid'>
                            <option value=''><?php $clang->eT("Choose Village"); ?></option>
                        </select>
                    </li>
                </ul>
            </fieldset>                    
            <fieldset style='clear:both;'>
                <legend><?php $clang->eT("Output Selection"); ?></legend>
                <li>
		<label class="checkbox" for="PotensiForm_katAll">
                    <input id="ytPotensiForm_katAll" type="hidden" value="0" name="PotensiForm[katAll]" />
                    All Fields                    
                    <span class="help-block error" id="PotensiForm_katAll_em_" style="display: none"></span>
                </label>		
                <input class="checkall" name="PotensiForm[katAll]" id="PotensiForm_katAll" value="1" type="checkbox" />
                </li>
                <li>
                <label class="checkbox" for="PotensiForm_kat3">
                    <input id="ytPotensiForm_kat3" type="hidden" value="0" name="PotensiForm[kat3]" />
                    III. Keterangan Umum Desa/Kelurahan                    
                    <span class="help-block error" id="PotensiForm_kat3_em_" style="display: none"></span>
                </label>
                <input text-align="left" name="PotensiForm[kat3]" id="PotensiForm_kat3" value="1" type="checkbox" />    
                </li>
                <li>
                <label class="checkbox" for="PotensiForm_kat4">
                    <input id="ytPotensiForm_kat4" type="hidden" value="0" name="PotensiForm[kat4]" />
                    IV. Kependudukan dan Ketenagakerjaan                    
                    <span class="help-block error" id="PotensiForm_kat4_em_" style="display: none"></span>
                </label>
                <input name="PotensiForm[kat4]" id="PotensiForm_kat4" value="1" type="checkbox" />
                </li>
                <li>
                <label class="checkbox" for="PotensiForm_kat5">
                    <input id="ytPotensiForm_kat5" type="hidden" value="0" name="PotensiForm[kat5]" />
                    V. Perumahan dan Lingkungan Hidup                    
                    <span class="help-block error" id="PotensiForm_kat5_em_" style="display: none"></span>
                </label>
                <input name="PotensiForm[kat5]" id="PotensiForm_kat5" value="1" type="checkbox" />
                </li>
                <li>    
                <label class="checkbox" for="PotensiForm_kat6">
                    <input id="ytPotensiForm_kat6" type="hidden" value="0" name="PotensiForm[kat6]" />
                    VI. Bencana Alam dan Penanganan Bencana Alam                    
                    <span class="help-block error" id="PotensiForm_kat6_em_" style="display: none"></span>
                </label>
                <input name="PotensiForm[kat6]" id="PotensiForm_kat6" value="1" type="checkbox" />
                </li>                    
                <li>
                <label class="checkbox" for="PotensiForm_kat7">
                    <input id="ytPotensiForm_kat7" type="hidden" value="0" name="PotensiForm[kat7]" />
                    VII. Pendidikan dan Kesehatan                    
                    <span class="help-block error" id="PotensiForm_kat7_em_" style="display: none"></span>
                </label>		
                <input name="PotensiForm[kat7]" id="PotensiForm_kat7" value="1" type="checkbox" />
                </li>                    
                <li>                   
                <label class="checkbox" for="PotensiForm_kat8">
                    <input id="ytPotensiForm_kat8" type="hidden" value="0" name="PotensiForm[kat8]" />
                    VIII. Sosial dan Budaya                    
                    <span class="help-block error" id="PotensiForm_kat8_em_" style="display: none"></span>
                </label>
                <input name="PotensiForm[kat8]" id="PotensiForm_kat8" value="1" type="checkbox" />    
                </li>                
                <li>                    
                <label class="checkbox" for="PotensiForm_kat9">
                    <input id="ytPotensiForm_kat9" type="hidden" value="0" name="PotensiForm[kat9]" />
                    IX. Hiburan dan Olah Raga
                    <span class="help-block error" id="PotensiForm_kat9_em_" style="display: none"></span>
                </label>
                <input name="PotensiForm[kat9]" id="PotensiForm_kat9" value="1" type="checkbox" />
                </li>                    
                <li>                    
                <label class="checkbox" for="PotensiForm_kat10">
                    <input id="ytPotensiForm_kat10" type="hidden" value="0" name="PotensiForm[kat10]" />
                    X. Angkutan, Komunikasi dan Informasi                    
                    <span class="help-block error" id="PotensiForm_kat10_em_" style="display: none"></span>
                </label>		
                <input name="PotensiForm[kat10]" id="PotensiForm_kat10" value="1" type="checkbox" />    
                </li>                    
                <li>                    
                <label class="checkbox" for="PotensiForm_kat12">
                    <input id="ytPotensiForm_kat12" type="hidden" value="0" name="PotensiForm[kat12]" />
                    XII. Penggunaan Lahan                    
                    <span class="help-block error" id="PotensiForm_kat12_em_" style="display: none"></span>
                </label>
                <input name="PotensiForm[kat12]" id="PotensiForm_kat12" value="1" type="checkbox" />
                </li>
                <li>
                    <label><?php $clang->eT("Select output format"); ?>:</label>
                    <input type='radio' id="outputtypehtml" name='outputtype' value='html' checked='checked' />
                    <label for='outputtypehtml'>HTML</label>
                    <input type='radio' id="outputtypepdf" name='outputtype' value='pdf' />
                    <label for='outputtypepdf'>PDF</label>
                    <input type='radio' id="outputtypexls" onclick='nographs();' name='outputtype' value='xls' />
                    <label for='outputtypexls'>Excel</label>
                </li>
            </fieldset>
        </div>
        <p>
            <input type='submit' value='<?php $clang->eT("View"); ?>' />
            <input type='button' value='<?php $clang->eT("Clear"); ?>' onclick="window.open('<?php echo Yii::app()->getController()->createUrl("admin/podes"); ?>', '_top')" />
        </p>
    </div>
     
    <!-- AUTOSCROLLING DIV CONTAINING QUESTION FILTERS -->            
</form>
<div style='clear: both'></div>
<?php
flush(); //Let's give the user something to look at while they wait for the pretty pictures
?>
<div class='header ui-widget-header header_statistics'>
    <div style='float:right'><img src='<?php echo $sImageURL; ?>/maximize.png' id='showrfilter' alt='<?php $clang->eT("Maximize"); ?>'/><img src='<?php echo $sImageURL; ?>/minimize.png' id='hiderfilter' alt='<?php $clang->eT("Minimize"); ?>'/></div>
    <?php $clang->eT("Result"); ?>
</div>

<div id='podesoutput' class='statisticsfilters'>
<?php echo "adfasdfasdf"//$output; ?>
</div>


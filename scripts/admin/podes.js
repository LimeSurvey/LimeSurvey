$(document).ready(function(){
    /* Show and hide the three major sections of the statistics page */
    /* The location filters */
    $('#hidelfilter').click( function(){
        $('#podeslocationfilters').hide(1000);
    });
    $('#showlfilter').click( function(){
        $('#podeslocationfilters').show(1000);
    });
    /* The actual podes results */
    $('#hiderfilter').click( function(){
        $('#podesoutput').hide(1000);
    });
    $('#showrfilter').click( function(){
        $('#podesoutput').show(1000);
    });
    /* Select all checkbox */
    $('.checkall').click(function () {
        $(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);
    });
    /* ajax call to fill province, district, sub-district, and village */
    $('#PotensiForm_provinsiid').change(function() {
        $.ajax({
            type: 'POST',
            url: "http://localhost/git-LimeSurvey/potensi/getkabupaten",
            data: {provinsiid : $('#PotensiForm_provinsiid').val()},
            success: function(data) {
                    $("#PotensiForm_kabupatenid").val(null).trigger("change"); 
                    $('#PotensiForm_kabupatenid option:gt(0)').remove();
                    $("#PotensiForm_kabupatenid").append(data);
            }
        });
    });

    $('#PotensiForm_kabupatenid').change(function() {
        $.ajax({
            type: 'POST',
            url: "http://localhost/git-LimeSurvey/potensi/getkecamatan",
            data: {kabupatenid : $('#PotensiForm_kabupatenid').val()},
            success: function(data) {
                    $("#PotensiForm_kecamatanid").val(null).trigger("change"); 
                    $('#PotensiForm_kecamatanid option:gt(0)').remove();
                    $("#PotensiForm_kecamatanid").append(data);
            }
        });
    });

    $('#PotensiForm_kecamatanid').change(function() {
        $.ajax({
            type: 'POST',
            url: "http://localhost/git-LimeSurvey/potensi/getdesa",
            data: {kecamatanid : $('#PotensiForm_kecamatanid').val()},
            success: function(data) {
                    $("#PotensiForm_desaid").val(null).trigger("change"); 
                    $('#PotensiForm_desaid option:gt(0)').remove();
                    $("#PotensiForm_desaid").append(data);
            }
        });
    });
});
    

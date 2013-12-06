set origin=%~dp0..\..\gringegreen\images

FOR %%G IN (%origin%\*.*) DO copy %%G %%~nxG
FOR %%G IN (%origin%\*.png) DO convert %%G -define modulate:colorspace=LCHuv -modulate 40,70,180 %%~nxG
FOR %%G IN (%origin%\*disabled*.png) DO convert %%G -define modulate:colorspace=LCHuv -modulate 100,70,180 %%~nxG
FOR %%G IN (%origin%\sort_asc.png %origin%\sort_desc.png %origin%\sort_none.png) DO convert %%G -define modulate:colorspace=LCHuv -modulate 100,70,190 %%~nxG

convert %origin%\search.gif -define modulate:colorspace=LCHuv -modulate 40,70,180 search.gif
convert %origin%\ajax-loader.gif -define modulate:colorspace=LCHuv -modulate 100,70,180 ajax-loader.gif
convert %origin%\ui-bg_glass_100_effbdb_1x400.png -define modulate:colorspace=LCHuv -modulate 100,70,90 ui-bg_glass_100_effbdb_1x400.png
ren ui-bg_glass_100_effbdb_1x400.png ui-bg_glass_100_ededfb_1x400.png

FOR %%G IN (%origin%\activate.png %origin%\deactivate.png %origin%\activate_deactivate_30.png %origin%\donate.png %origin%\expired.png %origin%\active.png %origin%\inactive.png %origin%\map.png %origin%\shadow.png) DO copy %%G %%~nxG


gimp statistics.png notyetstarted.png


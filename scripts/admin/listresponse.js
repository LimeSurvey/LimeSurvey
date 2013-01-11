//$Id: listsurvey.js 9692 2012-12-10 21:31:10Z pradesh $
//V1.1 Pradesh - Copied from listsurvey.js

$(document)
		.ready(
				function() {

					var old_owner = '';

					$("#addbutton")
							.click(
									function() {
										id = 2;
										html = "<tr name='joincondition_"
												+ id
												+ "' id='joincondition_"
												+ id
												+ "'><td><select name='join_"
												+ id
												+ "' id='join_"
												+ id
												+ "'><option value='and'>AND</option><option value='or'>OR</option></td><td></td></tr><tr><td><select name='field_"
												+ id
												+ "' id='field_"
												+ id
												+ "'>\n\
							<option value='completed'>"
												+ colNames[2]
												+ "</option>\n\
						<option value='id'>"
												+ colNames[3]
												+ "</option>\n\
						<option value='startlanguage'>"
												+ colNames[4]
												+ "</option>\n\
						</select>\n\</td>\n\<td>\n\
						<select name='condition_"
												+ id
												+ "' id='condition_"
												+ id
												+ "'>\n\
						<option value='equal'>"
												+ searchtypes[0]
												+ "</option>\n\
						<option value='contains'>"
												+ searchtypes[1]
												+ "</option>\n\
						<option value='notequal'>"
												+ searchtypes[2]
												+ "</option>\n\
						<option value='notcontains'>"
												+ searchtypes[3]
												+ "</option>\n\
						<option value='greaterthan'>"
												+ searchtypes[4]
												+ "</option>\n\
						<option value='lessthan'>"
												+ searchtypes[5]
												+ "</option>\n\
						</select></td>\n\<td><input type='text' id='conditiontext_"
												+ id
												+ "' style='margin-left:10px;' /></td>\n\
						<td><img src="
												+ minusbutton
												+ " onClick= $(this).parent().parent().remove();$('#joincondition_"
												+ id
												+ "').remove() id='removebutton'"
												+ id
												+ ">\n\
						<img src="
												+ addbutton
												+ " id='addbutton'  onclick='addcondition();' style='margin-bottom:4px'></td></tr><tr></tr>";
										$('#searchtable tr:last').after(html);
									});

					var searchconditions = {};
					var field;

					$('#searchbutton').click(function() {

					});
					var lastSel, lastSel2;
					function returnColModel() {
						if ($.cookie("detailedresponsecolumns")) {
							hidden = $.cookie("detailedresponsecolumns").split(
									'|');
							for (i = 0; i < hidden.length; i++)
								if (hidden[i] != "false")
									colModels[i]['hidden'] = true;
						}
						return colModels;
					}
					jQuery("#displayresponses").jqGrid({
						recordtext : sRecordText,
						emptyrecords : sEmptyRecords,
						pgtext : sPageText,
						loadtext : sLoadText,
						align : "center",
						url : jsonUrl,
						// editurl : editUrl,
						datatype : "json",
						mtype : "post",
						colNames : colNames,
						colModel : returnColModel(),
						toppager : true,
						height : "100%",
						width : screen.width - 20,
						shrinkToFit : false,
						ignoreCase : true,
						rowNum : 25,
						editable : true,
						scrollOffset : 0,
						sortable : true,
						hidegrid : false,
						sortname : 'sid',
						sortorder : 'asc',
						viewrecords : true,
						rowList : [ 25, 50, 100, 250, 500, 1000 ],
						multiselect : true,
						loadonce : true,
						pager : "#pager",
						caption : sCaption
					});
					jQuery("#displayresponses").jqGrid(
							'navGrid',
							'#pager',
							{
								deltitle : sDelTitle,
								searchtitle : sSearchTitle,
								refreshtitle : sRefreshTitle,
								add : false,
								del : false,
								edit : false,
								refresh : true,
								search : true
							},
							{},
							{},
							{
								msg : delmsg,
								bSubmit : sDelCaption,
								caption : sDelCaption,
								bCancel : sCancel,
								width : 700
							},
							{
								caption : sSearchCaption,
								Find : sFind,
								odata : [ sOperator1, sOperator2, sOperator3,
										sOperator4, sOperator5, sOperator6,
										sOperator7, sOperator8, sOperator9,
										sOperator10, sOperator11, sOperator12,
										sOperator13, sOperator14 ],
								Reset : sReset
							});
					jQuery("#displayresponses").jqGrid('filterToolbar', {
						searchOnEnter : false,
						defaultSearch : 'cn'
					});
					jQuery("#displayresponses")
							.jqGrid(
									'navButtonAdd',
									'#pager',
									{
										buttonicon : "ui-icon-calculator",
										caption : "",
										title : sSelectColumns,
										onClickButton : function() {
											jQuery("#displayresponses")
													.jqGrid(
															'columnChooser',
															{
																caption : sSelectColumns,
																bSubmit : sSubmit,
																bCancel : sCancel,
																done : function(
																		perm) {
																	if (perm) {
																		this
																				.jqGrid(
																						"remapColumns",
																						perm,
																						true);
																		var hidden = [];
																		$
																				.each(
																						$(
																								"#displayresponses")
																								.getGridParam(
																										"colModel"),
																						function(
																								key,
																								val) {
																							hidden
																									.push(val['hidden']);
																						});
																		hidden
																				.splice(
																						0,
																						1);
																		$
																				.cookie(
																						"detailedresponsecolumns",
																						hidden
																								.join("|"));
																	}
																}
															});
										}
									});

					jQuery("#displayresponses").jqGrid('gridResize', {
						minWidth : 1400,
						minHeight : 100
					});

					$('.wrapper').width($('#displayresponses').width() * 1.006);
					$('.footer').width(
							($('#displayresponses').width() * 1.006) - 10);

					/* Trigger the inline search when the access list changes */
					$('#gs_completed_select').change(
							function() {
								$("#gs_Completed").val(
										$('#gs_completed_select').val());

								var e = jQuery.Event("keydown");
								$("#gs_Completed").trigger(e);
							});

					/* Change the text search above "Status" icons to a dropdown */
					var parentDiv = $('#gs_completed').parent();
					parentDiv.prepend($('#gs_completed_select'));
					$('#gs_completed_select').css("display", "");
					$('#gs_Completed').css("display", "none");

					/* Disable search on the action column */
					var parentDiv = $('#gs_actions').parent();
					parentDiv.prepend($('#gs_no_filter'));
					$('#gs_no_filter').css("display", "");
					$('#gs_Actions').css("display", "none");

					var setTooltipsOnColumnHeader = function(grid, iColumn,
							text) {
						var col = iColumn + 1;
						var thd = jQuery("thead:first", grid[0].grid.hDiv)[0];
						jQuery("tr.ui-jqgrid-labels th:eq(" + col + ")", thd)
								.attr("title", text);
					};

					var colmodel_count = colModels.length;
					for (i = 0; i < colmodel_count; i++) {
						setTooltipsOnColumnHeader($("#displayresponses"), i,
								colModels[i]['title']);

					}

				});

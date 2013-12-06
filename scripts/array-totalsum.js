/*
 * @license This file is part of LimeSurvey
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

/**
 * Update column and line with sum in a text table
 *
 * @param {ids} if of the table
 * @param {_radix} number seperator
 */
 
function multi_set(ids,_radix)
{
	//quick ie check
	var ie=(navigator.userAgent.indexOf("MSIE")>=0)?true:false;
	//match for grand
	var _match_grand = new RegExp('grand');
	//match for total
	var _match_total = new RegExp('total');
    var radix = _radix; // comma, period, X (for not using numbers only)
    var numRegex = new RegExp('[^-' + radix + '0-9]','g');
	//main function (obj)
	//id = wrapper id
	function multi_total(id)
	{
		if(!document.getElementById(id)){return;};
		//alert('multi total called value ' + id);
		//generic vars
		//grand total 0 = none, 1 = horo, 2 = vert set if grand found
		var _grand = 0;
		//multi array holder
		var _bits = new Array();

		//var _obj = document.getElementById(id);
		//grab the tr's
		var _obj = document.getElementById(id);//.getElementsByTagName('table');

		//alert(_obj.length);
		var _tr = _obj.getElementsByTagName('tr');
		//counter used in top level of _bits array
		var _counter = 0;
		//generic for vars
		var _i = 0;
		var _l = _tr.length;
		for(_i=0; _i<_l; _i++)
		{
			//check we really have inputs to deal with
			if(_tr[_i].getElementsByTagName('input'))
			{
				var _td = _tr[_i].getElementsByTagName('td');
				//start building some nice arrays
				_bits.push(new Array());
				//clear the vert var set when total found in tr
				var vert =false;
				if(_tr[_i].className && _tr[_i].className.match(_match_total,'ig'))
				{
					//will need to set it up vertical
					vert = true;
				};
				//generic for vars for second level _bits[_i]
				var _a=0;
				var _al = _td.length;
				//alert(_al + ' ' + _i);
				if(_al > 0)
				{
				//	//counter for inner array
					var _tcounter=0;
					for(_a=0; _a < _al; _a++)
					{
						//only bother if we have inputs
						if(_td[_a].getElementsByTagName('input'))
						{
							//grab the first text input
							var _tdin = first_text(_td[_a].getElementsByTagName('input'));
							//check we got a text input
							if(_tdin)
							{
								//add it to the array @ counter
								_bits[_counter].push(_tdin);
								//set key board actions
								_tdin.onkeyup = calc;
								//check for total and grand total
								if(_td[_a].className && _td[_a].className.match(_match_total,'ig'))
								{
									//clear the key events with false returns
									_tdin.onkeydown = dummy;
									_tdin.onkeyup = dummy;
									//need to check for grand
									if(_td[_a].className.match(_match_grand,'ig'))
									{
										//set up a grand total
										if(vert && _bits[_counter].length > 1)
										{
											_grand=1;
                                            //run calc across last row
                                            calc_horo(_bits.length - 1);
										}
										else
										{
											_grand=2;
											_bits[_counter][_bits[0].length - 1]=_bits[_counter][0];
                                            //run calc on last col
                                            calc_vert(_bits[0].length - 1);
										}
									}
									else
									{
										//set up horo
										horo_set_up(_counter);
									};

								};
								if(vert && _grand == 0)
								{
									//deal with vert calc and clear the keyboard action
									_tdin.onkeydown = dummy;
									_tdin.onkeyup = dummy;
									vert_set_up(_tcounter);

								};
								_tcounter++;
							};
						};

					};
					//check we got some thing that time
					if(_bits[_counter].length == 0)
					{
						_bits.pop();
					}
					else
					{
						_counter++;
					}
				}
				else
				{
					//remove blanks
					_bits.pop();
				}

			};
		};
		//returns the first text input or false
		function first_text(arr)
		{
			var i=0;
			var l=arr.length;
			for(i=0; i<l; i++)
			{
				if(arr[i].getAttribute('type') && arr[i].getAttribute('type') == 'text')
				{
					return(arr[i]);
				}
			}
			return(false);
		}
		//sets up the horizontal calc
		function horo_set_up(id)
		{
			//make all in the row update the final
			//alert('set horo called for row ' + id);

			var i=0;
			var l=_bits[id].length;
			var qt=0;
			for(i=0; i<l; i++)
			{
				var addaclass=!_bits[id][i].getAttribute(ie ? 'className' : 'class') ? '' : _bits[id][i].getAttribute(ie ? 'className' : 'class') + ' ';
				_bits[id][i].setAttribute((ie ? 'className' : 'class'), addaclass + 'horo_' + id);
				_bits[id][i].onkeyup = calc;
				if(i == (l - 1))
				{
					_bits[id][i].value = round(qt,12)
				}
				else if(_bits[id][i].value)
				{
                    _aval=_bits[id][i].value;
                    if (radix===',') {
                        _aval = _aval.split(',').join('.');
                        _bits[id][i].value = _aval.split('.').join(',');
                    }
                    if  (_aval == parseFloat(_aval)) {
                        qt += +_aval;
                    }
				};
			};

		}
		//sets up the vertical calc
		function vert_set_up(id)
		{
			//alert('set vert called for col ' + id + ' ' + _bits.join('-'));
			id *= 1;
			var i=0;
			var l=_bits.length;
			var qt = 0;
			for(i=0; i<l; i++)
			{
				var addaclass=!_bits[i][id].getAttribute(ie ? 'className' : 'class') ? '' : _bits[i][id].getAttribute(ie ? 'className' : 'class') + ' ';
				_bits[i][id].setAttribute((ie ? 'className' : 'class'), addaclass + 'vert_' + id);
				_bits[i][id].onkeyup = calc;
				if(i == (l - 1))
				{
					_bits[i][id].value = round(qt,12);
				}
				else if(_bits[i][id].value)
				{
                    _aval=_bits[i][id].value;
                    if (radix===',') {
                        _aval = _aval.split(',').join('.');
                        _bits[i][id].value = _aval.split('.').join(',');
                    }
                    if  (_aval == parseFloat(_aval)) {
                        qt += +_aval;
                    }
				};
			};
		};
		//calculates a row or col or both
		//runs the grand totals if required
		function calc(e)
		{
			//alert('calc called ');
			e=(e)?e:event;
			var el=e.target||e.srcElement;
			var _id=el.getAttribute(ie ? 'className' : 'class');

            // eliminate bad numbers
            _aval=new String(el.value);
            if (radix!=='X') {
                _aval=_aval.replace(numRegex,'');
            }
            if (radix===',') {
                _aval = _aval.split(',').join('.');
            }
            if (radix!=='X' && _aval != '-' && _aval != '.' && _aval != '-.' && _aval != parseFloat(_aval)) {
                _aval = "";
            }
            if (radix===',') {
                el.value = _aval.split('.').join(',');
            }
            else if (radix!=='X') {
                el.value = _aval;
            }

			//vert_[id] horo_[id] in class trigger vert or horo calc on row[id]
			if(_id.match('vert_','ig'))
			{
				var vid = get_an_id(_id,'vert_');
				calc_vert(vid);
			};
			if(_id.match('horo_','ig'))
			{
				var hid = get_an_id(_id,'horo_');
				calc_horo(hid);
			};
			//check for grand total
			switch(_grand)
			{
				case 1:
				//run calc across last row
					calc_horo(_bits.length - 1);
				 	break;
				case 2:
				//run calc on last col
					calc_vert(_bits[0].length - 1);
					break;
			}
            checkconditions($(el).val(), $(el).attr('name'), $(el).attr('type'));
			return(true);
		};
		//retuns the id from end of string like 'vert_[id] horo_[id] other class'
		//_id = string
		//_break = string to break @
		function get_an_id(_id,_break)
		{
			var id = _id.split(_break);
			id[1] = id[1].split(' ');
			return(id[1][0] * 1);
		};
		//run vert calc on col[vid]
		function calc_vert(vid)
		{
			var i=0;
			var l=_bits.length;
			var qt = 0;
			//get or set the last ones id
			for(i=0; i<l; i++)
			{
				if(i == (l - 1))
				{
					//check if sum is a number
                    if(isNaN(qt))
                    {
                        _bits[i][vid].value = "Not a number";
                    }
                    else
                    {
                        _bits[i][vid].value = round(qt,12);
                    }
				}
				else if(_bits[i][vid].value)
				{
                    _aval=_bits[i][vid].value;
                    if (radix===',') {
                        _aval = _aval.split(',').join('.');
                    }
                    if  (_aval == parseFloat(_aval)) {
                        qt += +_aval;
                    }
				};
			};

		};
		//run horo calc on row[hid]
		function calc_horo(hid)
		{
			var i=0;
			var l=_bits[hid].length;
			var qt=0;
			for(i=0; i<l; i++)
			{
				if(i == (l - 1))
				{
					if (isNaN(qt))
                    {
                        _bits[hid][i].value = "Not a number"
                    }
                    else
                    {
                        _bits[hid][i].value = round(qt,12);
                    }
				}
				else if(_bits[hid][i].value)
				{
                    _aval=_bits[hid][i].value;
                    if (radix===',') {
                        _aval = _aval.split(',').join('.');
                    }
                    if  (_aval == parseFloat(_aval)) {
                        qt += +_aval;
                    }
				};
			};
		};
		//clear key input
		function dummy(e)
		{
			return(false);
		};
	};
	//set up the dom
	//alert('multi called called value ' + ids);
	ids = ids.split(',');
	//generic for vars
	var ii = 0;
	var ll=ids.length;
	//object place holder
	var _collection=new Array();

	for(ii=0; ii<ll; ii++)
	{
		//run main function per id
		_collection.push(new multi_total(ids[ii]));
	}
}

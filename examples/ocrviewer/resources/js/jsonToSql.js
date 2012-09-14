/*
	Input should look like this:
	{
		select: '',
		from: 'table',
		where: [
			{filter: 1, operator: '>', value: 'numeric' },
			{filter: 'nominal'}, // operator defaults to '='
			{filter: 2, operator: '~*', value: 'non-case sensitive' },
			{filter: 4, operator: '<=', value: 99 }
		],
		groupBy: 'variable',
		orderBy: 'column',
		last: true // appends ';'
		rejectEmpty: true // ignores '', 'undefined', and null
	}
*/
var jsonToSql = function( json ) {
	var sql = '';

	if ( json.select ) sql = 'SELECT ' + json.select;
	if ( json.from ) sql += ' FROM ' + json.from;
	if ( json.where ) sql += ' WHERE ' + generateWhereClause( json.where );
	if ( json.groupBy ) sql += ' GROUP BY ' + json.groupBy;
	if ( json.orderBy ) sql += ' ORDER BY ' + json.orderBy;
	if ( json.last ) sql += ';';
	return sql;
	function generateWhereClause( whereArray ) {
		var conditions = [];
		whereArray.forEach( function( item ) {
			var op = '=';
			var flag = true;
			if ( typeof item.value == 'object' ) {
				if ( !item.operator ) { item.operator = '=' }
			}
			if ( json.rejectEmpty ) {
				if ( item.value == '' || item.value == 'undefined' || item.value == null ) {
					flag = false;
				}
			}
			if ( item.operator == '=' || item.operator == '~*' ) item.value = '"'+item.value+'"';
			if ( flag ) conditions.push( item.filter + ' ' + item.operator + ' ' + item.value );
		});
		if ( conditions.length == 0 ) conditions.push( '1=1' );
		return conditions.join( ' and ' );
	}
}

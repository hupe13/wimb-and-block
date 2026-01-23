/**
 * Sort table wimb entries
 *
 * @package wimb-and-block
 **/

// https://stackoverflow.com/questions/59282842/how-to-make-sorting-html-tables-faster
function sortTableRowsByColumnChar( table, columnIndex, ascending ) {
	const rows           = Array.from( table.querySelectorAll( ':scope > tbody > tr' ) );
	rows.sort(
		( x, y ) =>
		{
			const xValue = x.cells[columnIndex].textContent;
			const yValue = y.cells[columnIndex].textContent;
			return ascending ? ( xValue.toLowerCase() < yValue.toLowerCase() ) : ( yValue.toLowerCase() < xValue.toLowerCase() );
		}
	);
	for ( let row of rows ) {
		table.tBodies[0].appendChild( row );
	}
}

// Source - https://stackoverflow.com/a
// Posted by Dai, modified by community. See post 'Timeline' for change history
// Retrieved 2026-01-21, License - CC BY-SA 4.0
function sortTableRowsByColumnNumbers( table, columnIndex, ascending ) {
	const rows               = Array.from( table.querySelectorAll( ':scope > tbody > tr' ) );
	rows.sort(
		( x, y ) =>
		{
				const xValue = x.cells[columnIndex].textContent;
				const yValue = y.cells[columnIndex].textContent;
				const xNum   = parseFloat( xValue );
				const yNum   = parseFloat( yValue );
				return ascending ? ( xNum - yNum ) : ( yNum - xNum );
		}
	);

	const fragment = new DocumentFragment();
	for ( let row of rows ) {
		fragment.appendChild( row );
	}
	table.tBodies[0].appendChild( fragment );
}

function onColumnHeaderClickedChar( ev ) {
	const th        = ev.currentTarget;
	const table     = th.closest( 'table' );
	const thIndex   = Array.from( th.parentElement.children ).indexOf( th );
	const ascending = ! ( 'sort' in th.dataset ) || th.dataset.sort != 'asc';
	const start     = performance.now();
	sortTableRowsByColumnChar( table, thIndex, ascending );
	const end = performance.now();
	console.log( "Sorted table rows in %d ms.",  end - start );
	const allTh = table.querySelectorAll( ':scope > thead > tr > th' );
	for ( let th2 of allTh ) {
		delete th2.dataset['sort'];
	}
	th.dataset['sort'] = ascending ? 'asc' : 'desc';
}

function onColumnHeaderClickedNumbers( ev ) {
	const th        = ev.currentTarget;
	const table     = th.closest( 'table' );
	const thIndex   = Array.from( th.parentElement.children ).indexOf( th );
	const ascending = ! ( 'sort' in th.dataset ) || th.dataset.sort != 'asc';
	const start     = performance.now();
	sortTableRowsByColumnNumbers( table, thIndex, ascending );
	const end = performance.now();
	console.log( "Sorted table rows in %d ms.",  end - start );
	const allTh = table.querySelectorAll( ':scope > thead > tr > th' );
	for ( let th2 of allTh ) {
		delete th2.dataset['sort'];
	}
	th.dataset['sort'] = ascending ? 'asc' : 'desc';
}

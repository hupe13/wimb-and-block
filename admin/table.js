/**
 * Color of table first last
 *
 * @package wimb-and-block
 */

var t = document.getElementById( 'statistics' );
if (t) {
	Array.from( t.rows ).forEach(
		(tr, rowIdx) =>
		{
			Array.from( tr.cells ).forEach(
				(cell, cellIdx) =>
				{
					if (cell.innerText == '') {

					} else if (cell.innerText >= 0 && cell.innerText <= 40) {
						cell.style.backgroundColor = '#d3d3d3';
					} else if (cell.innerText > 40 && cell.innerText <= 50) {
						cell.style.backgroundColor = '#c6c6c6';
					} else if (cell.innerText > 50 && cell.innerText <= 60) {
						cell.style.backgroundColor = '#b9b9b9';
					} else if (cell.innerText > 60 && cell.innerText <= 70) {
						cell.style.backgroundColor = '#adadad';
					} else if (cell.innerText > 70 && cell.innerText <= 80) {
						cell.style.backgroundColor = '#a0a0a0';
					} else if (cell.innerText > 80 && cell.innerText <= 90) {
						cell.style.backgroundColor = '#939393';
					} else if (cell.innerText > 90 && cell.innerText <= 100) {
						cell.style.backgroundColor = '#868686';
					}
				}
			);
		}
	);
}

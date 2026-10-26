{{-- Shared sparkline helper (ApexCharts v3.19.0, must already be loaded). Include inside @push('scripts') before calling dashSparkline() --}}
<script>
// type: 'area' | 'bar'. Bar width max ~10px, so a single bar never looks fat (v3.19.0 has no px width option)
function dashSparkline(selector, type, color, name, data, labels, isMoney) {
	var el = document.querySelector(selector);
	if (!el) { return; }
	var barPercent = 45;
	if (type === 'bar') {
		var slot = (el.clientWidth || 200) / Math.max(1, data.length);
		barPercent = Math.max(5, Math.min(45, Math.round(10 / slot * 100)));
	}
	var options = {
		series: [{ name: name, data: data }],
		chart: { type: type, height: 65, toolbar: { show: false }, zoom: { enabled: false }, sparkline: { enabled: true } },
		stroke: { show: true, width: type === 'bar' ? 0 : 2.4, curve: 'smooth' },
		// ApexCharts v3.19.0: borderRadius bar par supported nahi, endingShape 'rounded' hi chalta hai
		plotOptions: { bar: { horizontal: false, columnWidth: barPercent + '%', endingShape: 'rounded' } },
		dataLabels: { enabled: false },
		colors: [color],
		xaxis: { categories: labels },
		fill: { opacity: type === 'area' ? 0.35 : 1 },
		tooltip: {
			theme: 'dark',
			fixed: { enabled: false },
			x: { show: true },
			y: {
				title: { formatter: function () { return ''; } },
				formatter: function (v) { return isMoney ? 'Rs ' + Number(v).toLocaleString() : v; }
			},
			marker: { show: false }
		}
	};
	new ApexCharts(el, options).render();
}
</script>

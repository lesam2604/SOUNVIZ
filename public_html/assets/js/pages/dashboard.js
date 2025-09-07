let optionsProfileVisit = {
	annotations: {
		position: 'back'
	},
	dataLabels: {
		enabled:false
	},
	chart: {
		type: 'bar',
		height: 300
	},
	fill: {
		opacity:1
	},
	plotOptions: {
	},
	series: [{
		name: 'sales',
		data: [9000000,20000000,30000000,20000000,10000000,20000000,30000000,20000000,10000000,20000000,30000000,20000000]
	}],
	colors: '#435ebe',
	xaxis: {
		categories: ["Janvier","Fevrier","Mars","Avril","Mai","Juin","Juillet", "Aout","Septembre","Octobre","Novembre","Decembre"],
	},
	chartOptions: {
    exporting: {
      enabled: true,
      menu: {
        // Customize the menu items
        downloadPNG: 'Télécharger en PNG',
        downloadJPEG: 'Télécharger en JPEG',
        downloadPDF: 'Télécharger en PDF',
        downloadSVG: 'Télécharger en SVG',
        exportImage: 'Exporter l\'image',
        exportData: 'Exporter les données',
      },
      download: true,
      // svgURL: 'https://www.example.com', // Provide a URL for SVG download (if needed)
      // pngURL: 'https://www.example.com', // Provide a URL for PNG download (if needed)
      // jpegURL: 'https://www.example.com', // Provide a URL for JPEG download (if needed)
      // pdfURL: 'https://www.example.com', // Provide a URL for PDF download (if needed)
    },
  },
}
// let optionsVisitorsProfile  = {
// 	series: [70, 30],
// 	labels: ['Male', 'Female'],
// 	colors: ['#435ebe','#55c6e8'],
// 	chart: {
// 		type: 'donut',
// 		width: '100%',
// 		height:'350px'
// 	},
// 	legend: {
// 		position: 'bottom'
// 	},
// 	plotOptions: {
// 		pie: {
// 			donut: {
// 				size: '30%'
// 			}
// 		}
// 	}
// }

let optionsEurope = {
	series: [{
		name: 'series1',
		data: [310, 800, 600, 430, 540, 340, 605, 805,430, 540, 340, 605]
	}],
	chart: {
		height: 80,
		type: 'area',
		toolbar: {
			show:false,
		},
	},
	colors: ['#5350e9'],
	stroke: {
		width: 2,
	},
	grid: {
		show:false,
	},
	dataLabels: {
		enabled: false
	},
	xaxis: {
		type: 'datetime',
		categories: ["2018-09-19T00:00:00.000Z", "2018-09-19T01:30:00.000Z", "2018-09-19T02:30:00.000Z", "2018-09-19T03:30:00.000Z", "2018-09-19T04:30:00.000Z", "2018-09-19T05:30:00.000Z", "2018-09-19T06:30:00.000Z","2018-09-19T07:30:00.000Z","2018-09-19T08:30:00.000Z","2018-09-19T09:30:00.000Z","2018-09-19T10:30:00.000Z","2018-09-19T11:30:00.000Z"],
		axisBorder: {
			show:false
		},
		axisTicks: {
			show:false
		},
		labels: {
			show:false,
		}
	},
	show:false,
	yaxis: {
		labels: {
			show:false,
		},
	},
	tooltip: {
		x: {
			format: 'dd/MM/yy HH:mm'
		},
	},
};

let optionsAmerica = {
	...optionsEurope,
	colors: ['#008b75'],
}
let optionsIndonesia = {
	...optionsEurope,
	colors: ['#dc3545'],
}

let chartProfileVisit = new ApexCharts(document.querySelector("#chart-profile-visit"), optionsProfileVisit);
// var chartVisitorsProfile = new ApexCharts(document.getElementById('chart-visitors-profile'), optionsVisitorsProfile)
let chartEurope = new ApexCharts(document.querySelector("#chart-europe"), optionsEurope);
let chartAmerica = new ApexCharts(document.querySelector("#chart-america"), optionsAmerica);
let chartIndonesia = new ApexCharts(document.querySelector("#chart-indonesia"), optionsIndonesia);

// chartIndonesia.render();
// chartAmerica.render();
// chartEurope.render();
chartProfileVisit.render();
// chartVisitorsProfile.render();
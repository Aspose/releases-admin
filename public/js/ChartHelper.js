function RenderChartPie(title, dataPoints,container)
{
    //dp=[];

    //dp.push({ y: 4181563, indexLabel: "PlayStation 3" });
    //dp.push({ y: 2175498, indexLabel: "Wii" });
    var chart = new CanvasJS.Chart(container,
  {
      zoomEnabled: true,
      panEnabled: true,
      title: {
          text: title
      },
      legend: {
          maxWidth: 200,
          itemWidth: 120
      },
      data: [
      {
          type: "doughnut",
          showInLegend: false,
          legendText: "{indexLabel}",
          dataPoints: dataPoints
        
      }
      ]

     
  });


    chart.render();
    $(".canvasjs-chart-credit").remove();
}

function RenderColumnChart(title, dataPoints,target)
{

    var chart = new CanvasJS.Chart(target
        //"chartContainerColumn"
        ,
  {
      title: {
          text: title
      },
      
      data: [

      {
          type: "column",scaleShowVerticalLines: false,
          maintainAspectRatio: true,
          responsive: false,
          indexLabel: " {y}",
          indexLabelPlacement: "outside",  
          indexLabelOrientation: "horizontal",
          dataPoints: dataPoints// [
         
      }
      ]
  });

    chart.render();
    $(".canvasjs-chart-credit").remove();
}

function RenderBARChart(title, dataPoints, target) {

    var chart = new CanvasJS.Chart(target
        //"chartContainerColumn"
        ,
  {
      title: {
          text: title
      },

      data: [

      {
          type: "bar",
          maintainAspectRatio: true, scaleShowVerticalLines: false,
          responsive: false,
          indexLabel: " {y}",
          indexLabelPlacement: "outside",
          indexLabelOrientation: "horizontal",
          dataPoints: dataPoints// [

      }
      ]
  });

    chart.render();
    $(".canvasjs-chart-credit").remove();
}
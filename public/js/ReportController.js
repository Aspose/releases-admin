$(document).ready(function () {
    //DrawTotalChart();
    //DrawDetailedChart();
    //DrawFamilyPieChart();
    //DrawPopularFilesChart();
});

function GetBasePath() {
    return location.protocol + "//" + location.host + "/admin/";
}


function DrawTotalChart() {

    var serve = JSON.stringify({ date: "30" });


    $.ajax({
        type: "POST",
        contentType: "application/json;charset=utf-8",
        url: GetBasePath() + "AjaxDataService.asmx/GetGeneralStatus",
        dataType: "json",
        data: serve,
        success: function (msg) {
            var data = jQuery.parseJSON(msg.d);

            $("#TotalDownloads").html(data.TotalDownloads);
            $("#TotalDownloadsByCustomers").html(data.DownloadByCustomers);
            $("#TotalDownloadsByStaff").html(data.DownloadByAsposeStaffMember);
            dp = [];

            var Total = data.DownloadByAsposeStaffMember + data.DownloadByCustomers;


            dp.push({ y: data.DownloadByCustomers, indexLabel: "Customers - " + data.DownloadByCustomers });
            dp.push({ y: data.DownloadByAsposeStaffMember, indexLabel: "Staff Members - " + data.DownloadByAsposeStaffMember });
            RenderChartPie("Total Files Downloaded - " + Total, dp, "chartContainer");

        }

    });
}

function DrawDetailedChart() {
    var serve = JSON.stringify({ date: "30" });


    $.ajax({
        type: "POST",
        contentType: "application/json;charset=utf-8",
        url: GetBasePath() + "AjaxDataService.asmx/GetDetailedReport",
        dataType: "json",
        data: serve,
        success: function (msg) {
            dp = [];


            var data = jQuery.parseJSON(msg.d);

            for (var i = 0; i < data.length; i++) {
                dp.push({ y: parseInt(data[i].EntityCount), label: data[i].EntityName });
            }

            RenderColumnChart("Top 10 Products", dp, "chartContainerColumn");


        }

    });


}



function DrawFamilyPieChart() {

    var serve = JSON.stringify({ date: "30" });


    $.ajax({
        type: "POST",
        contentType: "application/json;charset=utf-8",
        url: GetBasePath() + "AjaxDataService.asmx/GetFamilyPIEChart",
        dataType: "json",
        data: serve,
        success: function (msg) {
            var data = jQuery.parseJSON(msg.d);

            console.log(msg.d);
            dp = [];


            for (var i = 0; i < data.length; i++) {
                dp.push({ y: parseInt(data[i].EntityCount), indexLabel: data[i].EntityName });
            }

            RenderChartPie("Most Downloaded Product Families", dp, "chartContainerFamily");


        }

    });
}

function DrawPopularFilesChart() {

    var serve = JSON.stringify({ date: "30" });


    $.ajax({
        type: "POST",
        contentType: "application/json;charset=utf-8",
        url: GetBasePath() + "AjaxDataService.asmx/GetPopularFiles",
        dataType: "json",
        data: serve,
        success: function (msg) {
            var data = jQuery.parseJSON(msg.d);


            dp = [];


            for (var i = 0; i < data.length; i++) {
                dp.push({ y: parseInt(data[i].EntityCount), label: data[i].EntityName });
            }

            RenderBARChart("Top 10 Releases", dp, "chartContainerPopular");


        }

    });
}




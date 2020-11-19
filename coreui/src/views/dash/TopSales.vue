<template>
    <div>
        <CCard>
            <CCardHeader
                @click="topSalesCollapse = !topSalesCollapse"
                class="btn btn-link btn-block text-left">
                Top Sales
            </CCardHeader>
            <CCollapse :show="topSalesCollapse">
                <CCardBody class="m-0">
                    <CTabs variant="pills" :active-tab="1">
                        <CTab title="By Category">
                            <CRow>
                                <CCol lg="6" sm="12">
                                    <highcharts :options="catPie"></highcharts>
                                </CCol>
                                <CCol lg="6" sm="12">
                                    <highcharts :options="catBar"></highcharts>
                                </CCol>
                            </CRow>
                        </CTab>
                        <CTab title="By Product">
                            <CRow>
                                <CCol lg="6" sm="12">
                                    <highcharts :options="productPie"></highcharts>
                                </CCol>
                                <CCol lg="6" sm="12">
                                    <highcharts :options="productBar"></highcharts>
                                </CCol>
                            </CRow>
                        </CTab>
                    </CTabs>
                </CCardBody>
            </CCollapse>
        </CCard>
    </div>
</template>


<script>
import axios from 'axios'
import { Chart } from 'highcharts-vue'

export default {
    name: 'TopSales',
    props: {
        date:{
            type: Object
        }
    },
    components:{
        highcharts: Chart
    },
    data () {
        return {
            topSalesCollapse: true,
            productPie:{
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                credits: { enabled: false},
                title: {
                    text: 'Product Sales Percentage'
                },
                tooltip: {
                    pointFormat: '<b>{point.percentage:.1f}%</b>'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: false
                        },
                        showInLegend: true
                    }
                },
                series: {
                    name: 'Remark',
                    // colorByPoint: true,
                    data: []
                },
            },
            productBar:{
                chart: {
                    type: 'column'
                },
                credits: { enabled: false},
                title: {
                    text: 'Product Sales'
                },
                xAxis: {
                    categories: [],
                    crosshair: true
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Pcs'
                    },
                    minTickInterval: 1
                },
                legend:{
                    enabled: false
                },
                tooltip: {
                    headerFormat: '<table>',
                    pointFormat: '<tr><td style="color:{series.color};padding:0">{point.key}: </td>' +
                        '<td style="padding:0"><b>{point.y} Pcs</b></td></tr>',
                    footerFormat: '</table>',
                    shared: true,
                    useHTML: true
                },
                plotOptions: {
                    column: {
                        pointPadding: 0.2,
                        borderWidth: 0
                    }
                },
                series: [{
                    name: 'Product Sales',
                    data: []
                }]
            },
            catPie:{
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie'
                },
                credits: { enabled: false},
                title: {
                    text: 'Category Sales Percentage'
                },
                tooltip: {
                    pointFormat: '<b>{point.percentage:.1f}%</b>'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: false
                        },
                        showInLegend: true
                    }
                },
                series: {
                    name: 'Remark',
                    // colorByPoint: true,
                    data: []
                },
            },
            catBar:{
                chart: {
                    type: 'column'
                },
                credits: { enabled: false},
                title: {
                    text: 'Category Sales'
                },
                xAxis: {
                    categories: [],
                    crosshair: true
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Pcs'
                    },
                    minTickInterval: 1
                },
                legend:{
                    enabled: false
                },
                tooltip: {
                    headerFormat: '<table>',
                    pointFormat: '<tr><td style="color:{series.color};padding:0">{point.key}: </td>' +
                        '<td style="padding:0"><b>{point.y} Pcs</b></td></tr>',
                    footerFormat: '</table>',
                    shared: true,
                    useHTML: true
                },
                plotOptions: {
                    column: {
                        pointPadding: 0.2,
                        borderWidth: 0
                    }
                },
                series: [{
                    name: 'Category Sales',
                    data: []
                }]
            }
        }
    },
    methods: {
        getChart(){
            let self = this;
            axios.post(  this.$apiAdress + '/api/report/dashboardSalesChart?token=' + localStorage.getItem("api_token"),
            {
                date: self.date,
            })
            .then(function (response) {
                self.productPie.series.data = response.data.pie;
                self.productBar.series[0].data = response.data.bar;
                self.productBar.xAxis.categories = response.data.productlabel;
                self.catPie.series.data = response.data.catpie;
                self.catBar.series[0].data = response.data.catbar;
                self.catBar.xAxis.categories = response.data.catlabel;
            }).catch(function (error) {
                console.log(error);
                self.$router.push({ path: '/login' });
            });
        }
    },
    mounted(){
        this.getChart()
    }
}
</script>

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
                        </CTab>
                        <CTab title="By Product">
                            <CRow>
                                <CCol lg="6" sm="12">
                                    <highcharts :options="productPie"></highcharts>
                                    <!-- <CChartPie
                                        :datasets="[
                                        {
                                            data: product.pie,
                                            backgroundColor: product.col,
                                            label: 'Product Sales'
                                        }
                                        ]"
                                        :labels="product.label"
                                    /> -->
                                </CCol>
                                <CCol lg="6" sm="12">
                                    <highcharts :options="productBar"></highcharts>
                                    <!-- <CChartBar
                                        :datasets="[
                                        {
                                            data: product.bar,
                                            backgroundColor: '#3399ff',
                                            label: 'Product Sales'
                                        }
                                        ]"
                                        :labels="product.label"
                                    /> -->
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
                title: {
                    text: 'Product Sales Percentage'
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
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
                        text: 'Cup'
                    }
                },
                tooltip: {
                    headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                    pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                        '<td style="padding:0"><b>{point.y:.1f} mm</b></td></tr>',
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
                // self.product.pie= response.data.pie;
                // self.product.bar= response.data.bar;
                // self.product.label= response.data.label;
                // self.product.col= response.data.col;
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

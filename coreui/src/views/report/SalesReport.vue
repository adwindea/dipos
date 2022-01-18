<template>
    <div>
        <CRow>
            <CCol col="12">
                <CCard>
                    <CCardHeader>
                        <CRow>
                            <CCol lg="5" md="4" xs="12">
                                <h4>Sales Report
                                    
                                </h4>
                            </CCol>
                            <CCol lg="5" md="6" xs="12">
                                <date-range-picker
                                    style="width:100%"
                                    ref="picker"
                                    opens="auto"
                                    singleDatePicker="range"
                                    :timePicker="false"
                                    :timePicker24Hour="false"
                                    :showWeekNumbers="false"
                                    :showDropdowns="true"
                                    :autoApply="true"
                                    v-model="date"
                                    @update="resetData"
                                >
                                    <template v-slot:input="picker" style="min-width: 350px;">
                                        {{ picker.startDate | date }} - {{ picker.endDate | date }}
                                    </template>
                                </date-range-picker>
                            </CCol>
                            <CCol lg="2" md="2" xs="12">
                                <downloadexcel
                                    class="btn btn-success btn-sm"
                                    :fetch="excelData"
                                    :fields="excelFields"
                                    :name="'Sales_Report_'+date.start_date+'_to_'+date.end_date+'.xls'">
                                    <CIcon :content="$options.excelIcon"></CIcon> Download Excel
                                </downloadexcel>
                            </CCol>
                        </CRow>
                    </CCardHeader>
                    <CCardBody>
                        <highcharts :options="salesChart"></highcharts>
                    </CCardBody>
                    <CCardBody>
                        <CDataTable
                        hover
                        sorter
                        :items="items"
                        :fields="fields"
                        :items-per-page="10"
                        items-per-page-select
                        pagination
                        >
                        </CDataTable>
                    </CCardBody>
                </CCard>
            </CCol>
        </CRow>
    </div>
</template>

<script>
import axios from 'axios'
import { Chart } from 'highcharts-vue'
import { cilSpreadsheet } from '@coreui/icons'
import DateRangePicker from 'vue2-daterange-picker'
import downloadexcel from "vue-json-excel"
import moment from 'moment'

export default {
    excelIcon: cilSpreadsheet,
    name: 'SalesReport',
    data () {
        return {
            date: {
                startDate: '',
                endDate: '',
            },
            excelFields : {
                'Date': 'sales_date',
                'Total Order': 'total_order',
                'CoGS': 'COGS',
                'Average': 'average',
                'Total Cup': 'total_cup'
            },
            fields : [
                {key:'sales_date', _classes:'text-center'},
                {key:'total_order', _classes:'text-center'},
                {key:'COGS', _classes:'text-center'},
                {key:'average', _classes:'text-center'},
            ],
            items: [],
            salesChart:{
                chart: {
                    type: 'column'
                },
                credits: { enabled: false},
                title: {
                    text: 'Sales Chart'
                },
                xAxis: {
                    categories: [],
                    crosshair: true
                },
                yAxis: [{
                    min: 0,
                    title: {
                        text: 'Order'
                    },
                    minTickInterval: 1,
                    opposite: true
                },{
                    min: 0,
                    title: {
                        text: 'CoGS'
                    },
                },{
                    min: 0,
                    title: {
                        text: 'Product Sold'
                    },
                    minTickInterval: 1,
                    opposite: true
                }
                ],
                tooltip: {
                    shared: true,
                },
                plotOptions: {
                    column: {
                        pointPadding: 0.05,
                        borderWidth: 0
                    }
                },
                series: [{
                    name: 'Order',
                    data: [],
                    tooltip: {
                        valueSuffix: ' Order'
                    },
                },{
                    name: 'CoGS',
                    data: [],
                    tooltip: {
                        valueSuffix: ' IDR'
                    },
                    yAxis: 1
                },{
                    name: 'CoGS per Order',
                    data: [],
                    tooltip: {
                        valueSuffix: ' IDR'
                    },
                    yAxis: 1,
                    type: 'spline',
                    color: 'purple'
                },{
                    name: 'Product Sold',
                    data: [],
                    tooltip: {
                        valueSuffix: ' Pcs'
                    },
                    yAxis: 2
                }]
            },
        }
    },
    filters: {
        date(val) {
            return val ? moment(val).format("DD MMMM YYYY") : "";
        }
    },
    components:{
        highcharts: Chart,
        downloadexcel,
        DateRangePicker,
    },
    methods: {
        defaultDate(){
            if(this.date.startDate == '' || this.date.endDate == ''){
                this.date.startDate = moment().subtract(1, 'month').format();
                this.date.endDate = moment().format();
            }
        },
        getChart(){
            let self = this;
            axios.post(  this.$apiAdress + '/api/report/salesReportChart?token=' + localStorage.getItem("api_token"),
            {
                date: self.date,
            })
            .then(function (response) {
                self.salesChart.series[0].data = response.data.order;
                self.salesChart.series[1].data = response.data.cogs;
                self.salesChart.series[2].data = response.data.cogsorder;
                self.salesChart.series[3].data = response.data.productsold;
                self.salesChart.xAxis.categories = response.data.cat;
            }).catch(function (error) {
                console.log(error);
                self.$router.push({ path: '/login' });
            });
        },
        getData(){
            let self = this;
            axios.post(  this.$apiAdress + '/api/report/salesReportData?token=' + localStorage.getItem("api_token"),
            {
                date: self.date,
            })
            .then(function (response) {
                self.items = response.data.order;
            }).catch(function (error) {
                console.log(error);
                self.$router.push({ path: '/login' });
            });
        },
        resetData(){
                this.getData()
                this.getChart()
        },
        async excelData(){
            let self = this;
            const response = await axios.post(this.$apiAdress + '/api/report/excelSalesReport?token=' + localStorage.getItem("api_token"),
            {
                date: self.date,
            });
            return response.data.order;
        }
    },
    mounted(){
        this.defaultDate()
        this.getData()
        this.getChart()
    }
}
</script>

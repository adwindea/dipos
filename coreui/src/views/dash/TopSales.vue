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
                                    <CChartPie
                                        :datasets="[
                                        {
                                            data: product.pie,
                                            backgroundColor: product.col,
                                            label: 'Product Sales'
                                        }
                                        ]"
                                        :labels="product.label"
                                    />
                                </CCol>
                                <CCol lg="6" sm="12">
                                    <CChartBar
                                        :datasets="[
                                        {
                                            data: product.bar,
                                            backgroundColor: '#3399ff',
                                            label: 'Product Sales'
                                        }
                                        ]"
                                        :labels="product.label"
                                    />
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
import { CChartPie } from '@coreui/vue-chartjs'
import { CChartBar } from '@coreui/vue-chartjs'

export default {
    name: 'TopSales',
    props: {
        date:{
            type: Object
        }
    },
    components:{
        CChartPie,
        CChartBar
    },
    data () {
        return {
            topSalesCollapse: true,
            product: {
                label: [],
                pie: [],
                bar: [],
                col: [],
            },
            category: {
                label: '',
                pie: '',
                bar: ''
            },
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
                self.product.pie= response.data.pie;
                self.product.bar= response.data.bar;
                self.product.label= response.data.label;
                self.product.col= response.data.col;
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

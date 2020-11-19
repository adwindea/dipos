<template>
    <div>
        <CRow alignHorizontal="end">
            <CCol lg="3" md="4" xs="12">
                <CInput type="date" v-model="date.start_date" @change="reloadComponent()"/>
            </CCol>
            <CCol lg="3" md="4" xs="12">
                <CInput type="date" v-model="date.end_date" @change="reloadComponent()"/>
            </CCol>
        </CRow>
        <Widget :key="widget_key" v-bind:date="date"/>
        <CRow>
            <CCol col="12">
                <TopSales :key="topSales_key" v-bind:date="date"/>
            </CCol>
        </CRow>
        <CRow>
            <CCol col="12">
                <SalesTable :key="salesTable_key" v-bind:date="date"/>
            </CCol>
        </CRow>
    </div>
</template>


<script>
import Widget from './dash/widget'
import SalesTable from './dash/SalesTable'
import TopSales from './dash/TopSales'

export default {
    name: 'Dashboard',
    components: {
        Widget,
        SalesTable,
        TopSales,
    },
    data () {
        return {
            date: {
                start_date: '',
                end_date: '',
            },
            widget_key: +new Date(),
            salesTable_key: +new Date(),
            topSales_key: +new Date(),
        }
    },
    methods: {
        defaultDate(){
            if(this.date.start_date == '' || this.date.end_date == ''){
                var today = new Date();
                var dd = String(today.getDate()).padStart(2, '0');
                var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
                var yyyy = today.getFullYear();
                today = yyyy + '-' + mm + '-' + dd;
                this.date.start_date = today
                this.date.end_date = today
            }
        },
        reloadComponent(){
            var start_date = Date.parse(this.date.start_date)
            var end_date = Date.parse(this.date.end_date)
            if(end_date < start_date){
                alert('End date must be greater than start date!');
            }else{
                this.widget_key += 1
                this.salesTable_key += 1
                this.topSales_key += 1
            }
        }
    },
    mounted(){
        this.defaultDate()
    }
}
</script>

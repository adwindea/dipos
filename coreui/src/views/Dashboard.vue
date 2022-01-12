<template>
    <div>
        <!-- <CRow alignHorizontal="end">
            <CCol lg="3" md="4" xs="12">
                <CInput type="date" v-model="date.start_date" @change="reloadComponent()"/>
            </CCol>
            <CCol lg="3" md="4" xs="12">
                <CInput type="date" v-model="date.end_date" @change="reloadComponent()"/>
            </CCol>
        </CRow> -->
        <CRow alignHorizontal="end">
            <CCol lg="4" md="6" xs="12">
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
                    @update="reloadComponent"
                >
                    <template v-slot:input="picker" style="min-width: 350px;">
                        {{ picker.startDate | date }} - {{ picker.endDate | date }}
                    </template>
                </date-range-picker>
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
import DateRangePicker from 'vue2-daterange-picker'
//you need to import the CSS manually
import 'vue2-daterange-picker/dist/vue2-daterange-picker.css'
import moment from 'moment'


export default {
    name: 'Dashboard',
    components: {
        Widget,
        SalesTable,
        TopSales,
        DateRangePicker,
    },
    data () {
        return {
            date: {
                startDate: '',
                endDate: '',
            },
            widget_key: +new Date(),
            salesTable_key: +new Date(),
            topSales_key: +new Date(),
        }
    },
    filters: {
        date(val) {
            return val ? moment(val).format("DD MMMM YYYY") : "";
        }
    },
    methods: {
        defaultDate(){
            if(this.date.startDate == '' || this.date.endDate == ''){
                this.date.startDate = moment().subtract(1, 'month').format();
                this.date.endDate = moment().format();
            }
        },
        reloadComponent(){
            this.widget_key += 1
            this.salesTable_key += 1
            this.topSales_key += 1
        }
    },
    created(){
        this.defaultDate()
    },
    mounted(){
        this.reloadComponent()
    }
}
</script>

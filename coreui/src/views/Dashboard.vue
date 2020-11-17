<template>
    <div>
        <CRow alignHorizontal="end">
            <CCol lg="3" md="4" xs="12">
                <CInput type="date" v-model="date.start_date"/>
            </CCol>
            <CCol lg="3" md="4" xs="12">
                <CInput type="date" v-model="date.end_date"/>
            </CCol>
        </CRow>
        <Widget v-bind:date="date"/>
    </div>
</template>


<script>
import Widget from './dash/widget'

export default {
    name: 'Dashboard',
    components: {
        Widget
    },
    data () {
        return {
            date: {
                start_date: '',
                end_date: ''
            }
        }
    },
    methods: {
        defaultDate(){
            if(this.date.start_date == '' || this.date.end_date == ''){
                var today = new Date();
                var dd = String(today.getDate()).padStart(2, '0');
                var dd2 = String(today.getDate() + 1).padStart(2, '0');
                var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
                var yyyy = today.getFullYear();
                today = yyyy + '-' + mm + '-' + dd;
                var tomorrow = yyyy + '-' + mm + '-' + dd2;
                this.date.start_date = today
                this.date.end_date = tomorrow
            }
        }
    },
    mounted(){
        this.defaultDate()
    }
}
</script>

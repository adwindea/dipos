<template>
    <CRow>
        <CCol col="12">
            <CCard>
                <CCardHeader>
                    <h3>
                        Add Promotion
                    </h3>
                </CCardHeader>
                <CCardBody>
                    <CRow>
                        <CCol>
                            <CAlert
                                :show.sync="dismissCountDown"
                                color="primary"
                                fade
                            >
                                ({{dismissCountDown}}) {{ message }}
                            </CAlert>
                        </CCol>
                    </CRow>
                    <CRow>
                        <CCol col="6">
                            <CInput label="Promotion Code" type="text" placeholder="Promotion Code" v-model="promotion.code"></CInput>
                            <CTextarea label="Note" placeholder="Type something here" v-model="promotion.note"></CTextarea>
                            <CSelect
                                label="Discount Type"
                                :value.sync="promotion.discount_type"
                                :plain="true"
                                :options="discount_type"
                                @change="discountType()"
                            />
                            <CInput label="Quantity" type="text" placeholder="Quantity" v-model="promotion.quantity" @keyup="separatize"></CInput>
                        </CCol>
                        <CCol col="6">
                            <CInput v-if="percentage" label="Amount" type="text" step="0.01" placeholder="Amount (%)" v-model="promotion.amount" @keyup="separatize"></CInput>
                            <CInput label="Min Buy" type="text" placeholder="Min Buy (IDR)" v-model="promotion.min_buy" @keyup="separatize"></CInput>
                            <CInput label="Max Discount" type="text" placeholder="Max Discount (IDR)" v-model="promotion.max_discount" @keyup="separatize"></CInput>
                            <CInput label="Start" type="date" v-model="promotion.start_date"/>
                            <CInput label="End" type="date" v-model="promotion.end_date"/>
                        </CCol>
                    </CRow>
                </CCardBody>
                <CCardFooter>
                    <CButton color="primary" @click="goBack()">Back</CButton>
                    <CButton class="float-right" color="primary" @click="store()">Create</CButton>
                </CCardFooter>
            </CCard>
        </CCol>
    </CRow>
</template>

<script>
import axios from 'axios'
export default {
    name: 'CreatePromotion',
    data () {
        return {
            promotion:{
                code: '',
                discount_type: 1,
                amount: '',
                quantity: '',
                note: '',
                min_buy: '',
                max_discount: '',
                start_date: '',
                end_date: '',
            },
            discount_type:[
                {label: 'Percentage', value: 1},
                {label: 'Fixed', value: 2},
            ],
            percentage: true,
            message: '',
            dismissSecs: 7,
            dismissCountDown: 0,
            showDismissibleAlert: false,
            divRestock: false,
        }
    },
    methods: {
        goBack() {
            this.$router.go(-1)
        },
        discountType(){
            if(this.promotion.discount_type == 1){
                this.percentage = true
            }else{
                this.percentage = false
            }
        },
        countDownChanged (dismissCountDown) {
            this.dismissCountDown = dismissCountDown
        },
        showAlert () {
            this.dismissCountDown = this.dismissSecs
        },
        store() {
            let self = this;
            axios.post(  this.$apiAdress + '/api/promotion/store?token=' + localStorage.getItem("api_token"),
                self.promotion
            )
            .then(function (response) {
                self.message = 'Successfully added promotion.';
                self.showAlert();
            }).catch(function (error) {
                if(error.response.data.message == 'The given data was invalid.'){
                    self.message = '';
                    for (let key in error.response.data.errors) {
                        if (error.response.data.errors.hasOwnProperty(key)) {
                            self.message += error.response.data.errors[key][0] + '  ';
                        }
                    }
                    self.showAlert();
                }else{
                    console.log(error);
                    self.$router.push({ path: 'login' });
                }
            });
        },
    },
    mounted(){
    }
}
</script>

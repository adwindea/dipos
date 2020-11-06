<style lang="scss">
    @import "../../assets/scss/product-card.scss";
</style>

<template>
    <CRow>
        <CCol col="12">
            <transition name="slide">
                <CRow>
                    <CCol col="5">
                        <CCard>
                            <CCardHeader>
                                <h4>Order {{ order.order_number }}</h4>
                            </CCardHeader>
                            <CCardBody>
                                <CCard class="mb-0">
                                    <CCardHeader
                                        @click="detailCollapse = !detailCollapse"
                                        class="btn btn-link btn-block text-left">
                                        Details
                                    </CCardHeader>
                                    <CCollapse :show="detailCollapse">
                                        <CCardBody class="m-0">
                                            <CInput label="Customer Name" type="text" placeholder="Customer Name" v-model="order.customer_name" required></CInput>
                                            <CInput label="Customer Email" type="email" placeholder="Customer Email" v-model="order.customer_email" required></CInput>
                                            <CTextarea label="Note" placeholder="Type something here" v-model="order.note"></CTextarea>
                                        </CCardBody>
                                    </CCollapse>
                                </CCard>
                                <CCard class="mb-0">
                                    <CCardHeader
                                        @click="openCart()"
                                        class="btn btn-link btn-block text-left">
                                        Cart
                                    </CCardHeader>
                                    <CCollapse :show="orderCollapse">
                                        <CCardBody class="m-0" style="height:42vh;overflow: auto;">
                                            <table class="table">
                                                <tr>
                                                    <th>Item</th>
                                                    <th class="text-center">Qty</th>
                                                    <th class="text-center">Total</th>
                                                </tr>
                                                <tr v-for="(item, $index) in order_items" :key="$index">
                                                    <td>{{ item.name }}</td>
                                                    <td class="text-center">
                                                        <CInput
                                                            size="sm"
                                                            type="number"
                                                            placeholder="Qty"
                                                            class="filter-number text-center"
                                                            v-model.number="item.quantity"
                                                            @keyup="saveQuantity($index)"
                                                            @blur="saveQuantity($index)"
                                                            >
                                                            <template #prepend>
                                                                <CButton size="sm" color="danger" @click="minusQuantity($index)"><CIcon name="cilMinus" height="14"/></CButton>
                                                            </template>
                                                            <template #append>
                                                                <CButton size="sm" color="success" @click="plusQuantity($index)"><CIcon name="cilPlus" height="14"/></CButton>
                                                            </template>
                                                        </CInput>
                                                    </td>
                                                    <td class="text-center">{{ item.quantity*item.price }}</td>
                                                </tr>
                                                <infinite-loading spinner="waveDots" :identifier="orderInfId" @infinite="getOrderItems">
                                                    <span slot="no-more"></span>
                                                </infinite-loading>
                                            </table>
                                        </CCardBody>
                                    </CCollapse>
                                </CCard>
                            </CCardBody>
                            <CCardFooter>
                                <CButton color="primary" class="float-right" @click="submitOrder( order.uuid )">Submit Order</CButton>
                            </CCardFooter>
                        </CCard>
                    </CCol>
                    <CCol col="7">
                        <CRow>
                        <CCol col="4">
<div class="wrapper">
  <div class="container">
    <div class="top"></div>
    <div class="bottom">
      <div class="left">
        <div class="details">
          <h1>Chair</h1>
          <p>£250</p>
        </div>
        <div class="buy"><CIcon name="cilCart"></CIcon></div>
      </div>
      <div class="right">
        <div class="done"><i class="material-icons">done</i></div>
        <div class="details">
          <h1>Chair</h1>
          <p>Added to your cart</p>
        </div>
        <div class="remove"><CIcon name="cilTrash"></CIcon></div>
      </div>
    </div>
  </div>
  <div class="inside">
    <div class="icon"><CIcon name="cilCart"></CIcon></div>
    <div class="contents">
      <table>
        <tr>
          <th>Width</th>
          <th>Height</th>
        </tr>
        <tr>
          <td>3000mm</td>
          <td>4000mm</td>
        </tr>
        <tr>
          <th>Something</th>
          <th>Something</th>
        </tr>
        <tr>
          <td>200mm</td>
          <td>200mm</td>
        </tr>
        <tr>
          <th>Something</th>
          <th>Something</th>
        </tr>
        <tr>
          <td>200mm</td>
          <td>200mm</td>
        </tr>
        <tr>
          <th>Something</th>
          <th>Something</th>
        </tr>
        <tr>
          <td>200mm</td>
          <td>200mm</td>
        </tr>
      </table>
    </div>
  </div>
</div>
</CCol>
</CRow>
                    </CCol>
                </CRow>
            </transition>
        </CCol>
    </CRow>
</template>


<script>
import axios from 'axios'
import InfiniteLoading from 'vue-infinite-loading'
export default {
    name: 'Order',
    data () {
        return {
            // fields:[
            //     {key:'item'},
            //     {key:'qty', _classes:'text-center'},
            //     {key:'total', _classes:'text-center'},
            // ],
            order: {
                customer_name: '',
                customer_email: '',
                uuid: '',
                order_number: '',
                note: '',
                price_total: ''
            },
            order_uuid: '',
            order_items: [],
            total_price: '',
            items: [],
            detailCollapse: false,
            orderCollapse: true,
            orderPage: 1,
            orderInfId: +new Date(),
        }
    },
    methods: {
        getOrderDetail (){
            let self = this;
            axios.get(  this.$apiAdress + '/api/order?token=' + localStorage.getItem("api_token"))
            .then(function (response) {
                self.order= response.data.order;
                self.resetOrderItem();
            }).catch(function (error) {
                console.log(error);
                self.$router.push({ path: '/login' });
            });
        },
        getOrderItems($state) {
            let self = this
            if(self.order.uuid !== ''){
                axios.get(this.$apiAdress + '/api/order/orderItems?token=' + localStorage.getItem("api_token"), {
                    params: {
                        page: self.orderPage,
                        uuid: self.order.uuid
                    },
                }).then(({ data }) => {
                    if (data.data.length) {
                        self.orderPage += 1;
                        self.order_items.push(...data.data);
                        $state.loaded();
                    } else {
                        $state.complete();
                    }
                });
            }
        },
        resetOrderItem(){
            this.orderPage = 1
            this.orderInfId += 1
            this.order_items = []
        },
        // setInputFilter(textbox, inputFilter) {
        //     ["input", "keydown", "keyup", "mousedown", "mouseup", "select", "contextmenu", "drop"].forEach(function(event) {
        //         textbox.addEventListener(event, function() {
        //             if (inputFilter(this.value)) {
        //                 this.oldValue = this.value;
        //                 this.oldSelectionStart = this.selectionStart;
        //                 this.oldSelectionEnd = this.selectionEnd;
        //             } else if (this.hasOwnProperty("oldValue")) {
        //                 this.value = this.oldValue;
        //                 this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
        //             } else {
        //                 this.value = "";
        //             }
        //         });
        //     });
        // },
        saveQuantity(index){
            let self = this
            // this.setInputFilter(document.getElementsByClassName("filter-number"), function(value) {
            //     return /^\d*\.?\d*$/.test(value); // Allow digits and '.' only, using a RegExp
            // });
            var uuid = self.order_items[index].uuid;
            var quantity = self.order_items[index].quantity;
            axios.post(  this.$apiAdress + '/api/order/saveQuantity?token=' + localStorage.getItem("api_token"),
                {
                    uuid: uuid,
                    quantity: quantity,
                }
            )
            .then(function (response) {

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
        openCart(){
            this.orderCollapse = !this.orderCollapse
            if(this.orderCollapse){
                this.resetOrderItem()
            }
        },
        plusQuantity(index){
            this.order_items[index].quantity++;
            this.saveQuantity(index);
        },
        minusQuantity(index){
            this.order_items[index].quantity--;
            this.saveQuantity(index);
        }
    },
    components:{
        InfiniteLoading,
    },
    mounted(){
        this.getOrderDetail();
    }
}
</script>

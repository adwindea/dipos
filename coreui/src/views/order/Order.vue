<style lang="scss">
    @import "../../assets/scss/product-card.scss";
    @import "../../assets/scss/print.scss";
</style>

<template>
    <CRow>
        <CCol col="12">
            <CModal
            title="Print Receipt"
            :show.sync="printModal"
            >
                <div class="ticket">
            <!-- <img src="./logo.png" alt="Logo"> -->
            <p class="centered">ini logo</p>
            <p class="centered">RECEIPT EXAMPLE
                <br>Address line 1
                <br>Address line 2</p>
            <table>
                <thead>
                    <tr>
                        <th class="quantity">Q.</th>
                        <th class="description">Description</th>
                        <th class="price">$$</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="quantity">1.00</td>
                        <td class="description">ARDUINO UNO R3</td>
                        <td class="price">$25.00</td>
                    </tr>
                    <tr>
                        <td class="quantity">2.00</td>
                        <td class="description">JAVASCRIPT BOOK</td>
                        <td class="price">$10.00</td>
                    </tr>
                    <tr>
                        <td class="quantity">1.00</td>
                        <td class="description">STICKER PACK</td>
                        <td class="price">$10.00</td>
                    </tr>
                    <tr>
                        <td class="quantity"></td>
                        <td class="description">TOTAL</td>
                        <td class="price">$55.00</td>
                    </tr>
                </tbody>
            </table>
            <p class="centered">Thanks for your purchase!
                <br>parzibyte.me/blog</p>
        </div>
                <footer slot="footer">
                    <CButton color="warning" class="text-center" @click="printReceipt()">Print</CButton>
                </footer>
            </CModal>
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
                                            <CInput label="Customer Name" type="text" placeholder="Customer Name" v-model="order.customer_name" @keyup="saveDetail()"></CInput>
                                            <CInput label="Customer Email" type="email" placeholder="Customer Email" v-model="order.customer_email" @keyup="saveDetail()"></CInput>
                                            <CTextarea label="Note" placeholder="Type something here" v-model="order.note" @keyup="saveDetail()"></CTextarea>
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
                                                    <th class="text-center" style="width:125px">Qty</th>
                                                    <th class="text-center">Total</th>
                                                </tr>
                                                <tr v-for="(item, $index) in order_items" :key="$index">
                                                    <td>{{ item.name }}</td>
                                                    <td>
                                                        <CInput
                                                            size="sm"
                                                            type="number"
                                                            placeholder="Qty"
                                                            addInputClasses="text-center"
                                                            v-model.number="item.quantity"
                                                            @keyup="saveQuantity($index)"
                                                            @blur="saveQuantity($index)"
                                                            v-bind:readonly="item.saved"
                                                            >
                                                            <template #prepend v-if="!item.saved">
                                                                <CButton size="sm" color="danger" @click="minusQuantity($index)"><CIcon name="cilMinus" height="14"/></CButton>
                                                            </template>
                                                            <template #append v-if="!item.saved">
                                                                <CButton size="sm" color="success" @click="plusQuantity($index)"><CIcon name="cilPlus" height="14"/></CButton>
                                                            </template>
                                                        </CInput>
                                                    </td>
                                                    <td class="text-center">{{ item.quantity*item.price }}</td>
                                                </tr>
                                                <tr>
                                                    <th colspan="2" >Total</th>
                                                    <th class="text-center">{{ order.price_total }}</th>
                                                </tr>
                                                <infinite-loading spinner="waveDots" :identifier="orderInfId" @infinite="getOrderItems">
                                                    <span slot="no-more"></span>
                                                </infinite-loading>
                                            </table>
                                        </CCardBody>
                                    </CCollapse>
                                </CCard>
                            </CCardBody>
                            <CCardFooter align='center'>
                                <CButton color="danger" @click="closeOrder( order.uuid )">Close</CButton>
                                <CButton color="warning" @click="printModal = true">Print</CButton>
                                <CButton color="primary" @click="saveOrder( order.uuid )">Save</CButton>
                            </CCardFooter>
                        </CCard>
                    </CCol>
                    <CCol col="7">
                        <!-- <CRow>
                            <CCol>
                                <CInput placeholder="Search" v-model="itemSearch" @keyup="resetListItem()">
                                    <template #append-content><CIcon name="cilMagnifyingGlass"/></template>
                                </CInput>
                            </CCol>
                        </CRow> -->
                        <CRow>
                            <CCol col="6">
                                <CSelect
                                    :value.sync="categorySearch"
                                    :plain="true"
                                    :options="categories"
                                    @change="resetListItem()"
                                />
                            </CCol>
                            <CCol col="6">
                                <CInput placeholder="Search" v-model="itemSearch" @keyup="resetListItem()">
                                    <template #append-content><CIcon name="cilMagnifyingGlass"/></template>
                                </CInput>
                            </CCol>
                            <!-- <div> -->
                                <CCol  md="6" lg="4" xl="3" v-for="(item, $index) in items" :key="$index">
                                    <div class="pc-wrapper">
                                        <div class="pc-container">
                                            <div class="top" v-bind:style="{height: '80%', width:'100%',
                                            background: 'url('+item.img+') no-repeat center center',
                                            webkitBackgroundSize: '100%',
                                            mozBackgroundSize: '100%',
                                            oBakcgroundSize: '100%',
                                            backgroundSize: '100%'
                                            }"></div>
                                            <div class="bottom" :id="'click'+item.uuid">
                                                <div class="left">
                                                    <div class="details">
                                                        <h6>{{item.name}}</h6>
                                                        <small>{{item.price}} IDR</small>
                                                    </div>
                                                    <div class="buy" @click="addClick(item.uuid)"><CIcon name="cilCart"></CIcon></div>
                                                </div>
                                                <div class="right">
                                                    <div class="done"><CIcon name="cilCheck"></CIcon></div>
                                                    <div class="details">
                                                        <h6>{{item.name}}</h6>
                                                        <small>Added to cart</small>
                                                    </div>
                                                    <div class="remove" @click="removeClick(item.uuid)"><CIcon name="cilX"></CIcon></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="inside">
                                            <div class="icon"><CIcon name="cilInfo"></CIcon></div>
                                            <div class="contents">

                                            </div>
                                        </div>
                                    </div>

                                </CCol>
                            <!-- </div> -->
                            <infinite-loading spinner="waveDots" :identifier="itemInfId" @infinite="getListItems">
                                <span slot="no-more"></span>
                            </infinite-loading>
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
            categories: [],
            detailCollapse: false,
            orderCollapse: true,
            orderPage: 1,
            orderInfId: +new Date(),
            itemSearch: '',
            categorySearch: '',
            itemPage: 1,
            itemInfId: +new Date(),
            printModal: false,
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
        saveDetail(){
            let self = this;
            axios.post(  this.$apiAdress + '/api/order/saveOrder?token=' + localStorage.getItem("api_token"),
                {
                    uuid: self.order.uuid,
                    customer_name: self.order.customer_name,
                    customer_email: self.order.customer_email,
                    note: self.order.note,
                }
            )
            .then(function (response) {

            }).catch(function (error) {
                if(error.response.data.message == 'The given data was invalid.'){
                    for (let key in error.response.data.errors) {
                        if (error.response.data.errors.hasOwnProperty(key)) {
                        self.message += error.response.data.errors[key][0] + '  ';
                        }
                    }
                }else{
                    console.log(error);
                    self.$router.push({ path: 'login' });
                }
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
        getListItems($state) {
            let self = this
            axios.get(this.$apiAdress + '/api/order/listItems?token=' + localStorage.getItem("api_token"), {
                params: {
                    page: self.itemPage,
                    searchkey: self.itemSearch,
                    searchcategory: self.categorySearch
                },
            }).then(({ data }) => {
                if (data.data.length) {
                    self.itemPage += 1;
                    self.items.push(...data.data);
                    $state.loaded();
                } else {
                    $state.complete();
                }
            });
        },
        getCategories (){
            let self = this;
            axios.get(  this.$apiAdress + '/api/order/getCategories?token=' + localStorage.getItem("api_token"))
            .then(function (response) {
                self.categories = response.data.categories;
            }).catch(function (error) {
                console.log(error);
                self.$router.push({ path: '/login' });
            });
        },
        resetOrderItem(){
            this.orderPage = 1
            this.orderInfId += 1
            this.order_items = []
        },
        resetListItem(){
            this.itemPage = 1
            this.itemInfId += 1
            this.items = []
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
                    order_uuid: self.order.uuid
                }
            )
            .then(function (response) {
                self.order = response.data.order
                if(response.data.reload){
                    self.resetOrderItem()
                }
            })
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
        },
        addClick(uuid){
            document.getElementById('click'+uuid).classList.add('clicked');
            let self = this
            axios.post(  this.$apiAdress + '/api/order/addOrderItem?token=' + localStorage.getItem("api_token"),
                {
                    uuid: uuid,
                    order_uuid: self.order.uuid
                }
            )
            .then(function (response) {
                self.order = response.data.order
                self.resetOrderItem()
            })
        },
        removeClick(uuid){
            document.getElementById('click'+uuid).classList.remove('clicked');
            let self = this
            axios.post(  this.$apiAdress + '/api/order/removeOrderItem?token=' + localStorage.getItem("api_token"),
                {
                    uuid: uuid,
                    order_uuid: self.order.uuid
                }
            )
            .then(function (response) {
                self.order = response.data.order
                self.resetOrderItem()
            })
        },
        printReceipt(){
            window.print()
        },
    },
    components:{
        InfiniteLoading,
    },
    mounted(){
        this.getOrderDetail();
        this.getCategories();
    }
}
</script>

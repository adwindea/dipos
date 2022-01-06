<style lang="scss">
    @import "../../assets/scss/product-card.scss";
    @import "../../assets/scss/floating-button.scss";
</style>

<template>
    <CRow>
        <CCol col="12">
            <CModal
            title="Close Order"
            :show.sync="closeModal"
            >
                <p>Are you sure to close order {{order.order_number}}?</p>
                <footer slot="footer">
                    <CButton color="danger" class="text-center" @click="saveOrder(order.uuid,2)">Close Order</CButton>
                </footer>
            </CModal>

            <CModal
            :title="'Order '+order.order_number"
            size="lg"
            :show.sync="cartModal"
            >
                <CTabs>
                    <CTab title="Cart" active>
                        <table class="table">
                            <tr>
                                <th style="min-width:125px;">Item</th>
                                <th class="text-center" style="min-width:125px">Qty</th>
                                <th style="min-width:125px;">Note</th>
                                <th class="text-center">Total</th>
                            </tr>
                            <tr v-for="(item, $index) in order_items" :key="$index">
                                <td>{{ item.name }}</td>
                                <td>
                                    <CInput
                                        size="sm"
                                        type="text"
                                        placeholder="Qty"
                                        addInputClasses="text-center"
                                        v-model.number="item.quantity"
                                        @keyup.enter="saveQuantity($index)"
                                        @blur="saveQuantity($index)"
                                        >
                                        <template #prepend>
                                            <CButton size="sm" color="danger" @click="minusQuantity($index)"><CIcon name="cilMinus" height="12"/></CButton>
                                        </template>
                                        <template #append>
                                            <CButton size="sm" color="success" @click="plusQuantity($index)"><CIcon name="cilPlus" height="12"/></CButton>
                                        </template>
                                    </CInput>
                                </td>
                                <td>
                                    <CInput
                                        size="sm"
                                        type="text"
                                        placeholder="Add note here"
                                        addInputClasses="text-center"
                                        v-model="item.note"
                                        @keyup.enter="saveQuantity($index)"
                                        @blur="saveQuantity($index)"
                                    />
                                </td>
                                <td class="text-center">{{ item.quantity*item.price_unformat }}</td>
                            </tr>
                            <tr>
                                <th colspan="3" >Total</th>
                                <th class="text-center">{{ order.final_price }}</th>
                            </tr>
                        </table>
                    </CTab>
                    <CTab title="Detail">
                        <div class="pt-3">
                            <CInput label="Customer Name" type="text" placeholder="Customer Name" v-model="order.customer_name" @blur="saveDetail()"></CInput>
                            <CInput label="Customer Email" type="email" placeholder="Customer Email" v-model="order.customer_email" @blur="saveDetail()"></CInput>
                            <CTextarea label="Note" placeholder="Type something here" v-model="order.note" @blur="saveDetail()"></CTextarea>
                            <CSelect
                                label="Payment"
                                :value.sync="order.payment_type"
                                :plain="true"
                                :options="payment_type"
                                @change="saveDetail()"
                            />
                            <CInput
                                label="Promo"
                                type="text"
                                placeholder="Code"
                                :description="promotion_warning"
                                v-model="promotion.code"
                                >
                                <template #append>
                                    <CButton color="success" @click="checkPromotion()">Check</CButton>
                                </template>
                            </CInput>
                        </div>
                    </CTab>
                </CTabs>
                <footer slot="footer">
                    <CButton color="danger" @click="closeModal = true; cartModal = false">Close Order</CButton>
                    <CButton color="warning" @click="printReceipt()">Print Receipt</CButton>
                    <CButton color="primary" @click="saveOrder(order.uuid,1)">Save Order</CButton>
                </footer>
            </CModal>


            <transition name="slide">
                <CRow>
                    <CCol col="12">
                        <CRow>
                            <CCol md="6">
                                <CSelect
                                    :value.sync="categorySearch"
                                    :plain="true"
                                    :options="categories"
                                    @change="resetListItem()"
                                />
                            </CCol>
                            <CCol md="6">
                                <CInput placeholder="Search" v-model="itemSearch" @keyup="resetListItem()">
                                    <template #append-content><CIcon name="cilMagnifyingGlass"/></template>
                                </CInput>
                            </CCol>
                            <!-- <div> -->
                                <CCol col="6" xxxs="12" xxs="6" xs="4" md="3" lg="3" xl="2" class="p-1" v-for="(item, $index) in items" :key="$index">
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
                                                        <h6 class="scroll-text">{{item.name}}</h6>
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
                                                <CRow>
                                                    <CCol>
                                                        <h6 style="color:white;">{{item.name}}</h6>
                                                    </CCol>
                                                </CRow>
                                                <CRow>
                                                    <CCol>
                                                        <p style="color:white;">{{item.price}} IDR</p>
                                                    </CCol>
                                                </CRow>
                                                <CRow class="pt-2">
                                                    <CCol>
                                                        <div style="white-space: pre; color: white;">{{item.description}}</div>
                                                    </CCol>
                                                </CRow>
                                            </div>
                                        </div>
                                    </div>

                                </CCol>
                            <!-- </div> -->
                            <infinite-loading spinner="waveDots" :identifier="itemInfId" @infinite="getListItems">
                                <span slot="no-more"></span>
                            </infinite-loading>
                        </CRow>
                        <CButton color="primary" @click="openCart()" class="cart-button-float"><CIcon name="cilCart" class="my-cart-button-float" height="18"/></CButton>
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
    name: 'CreateOrder',
    data () {
        return {
            order: {
                customer_name: '',
                customer_email: '',
                uuid: '',
                order_number: '',
                note: '',
                price_total: '',
                discount: '',
                discount_type: '',
                promotion_id: '',
                final_price: '',
                payment_type: ''
            },
            cartModal: false,
            order_items: [],
            items: [],
            categories: [],
            promotion: {
                code: ''
            },
            promotion_warning: '',
            payment_type: [
                {label: 'Cash', value:0},
                {label: 'QRIS', value:1},
                {label: 'Debt', value:2}
            ],
            itemSearch: '',
            categorySearch: '',
            itemPage: 1,
            itemInfId: +new Date(),
            printModal: false,
            closeModal: false,
            currentUser: '',
            nowTime: '',
        }
    },
    methods: {
        getOrderDetail (){
            let self = this;
            axios.get(  this.$apiAdress + '/api/order/create?token=' + localStorage.getItem("api_token"))
            .then(function (response) {
                self.order= response.data.order;
                self.promotion= response.data.promo;
                self.currentUser= response.data.user;
                self.getOrderItems();
            }).catch(function (error) {
                console.log(error);
                self.$router.push({ path: '/login' });
            });
        },
        saveDetail(){
            let self = this;
            axios.post(  this.$apiAdress + '/api/order/saveOrderDetail?token=' + localStorage.getItem("api_token"),
                {
                    uuid: self.order.uuid,
                    customer_name: self.order.customer_name,
                    customer_email: self.order.customer_email,
                    note: self.order.note,
                    payment_type: self.order.payment_type,
                    promotion_id: self.order.promotion_id,
                    discount_type: self.order.discount_type,
                }
            )
            .then(function (response) {
                self.order = response.data.order
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
        checkPromotion(){
            let self = this
            axios.post(  this.$apiAdress + '/api/order/checkPromotion?token=' + localStorage.getItem("api_token"),
                {
                    code: self.promotion.code,
                    order_uuid: self.order.uuid,
                }
            )
            .then(function (response) {
                if(response.data.status){
                    self.promotion = response.data.promo
                    self.order.promotion_id = response.data.promo.id
                    self.order.discount_type = response.data.promo.discount_type
                }else{
                    self.promotion = {}
                    self.order.promotion_id = ''
                    self.order.discount_type = ''
                }
                self.saveDetail()
                self.promotion_warning = response.data.mess
            })
        },
        openCart(){
            this.cartModal = true
            this.getOrderItems()
        },
        getOrderItems() {
            let self = this
            if(self.order.uuid !== ''){
                axios.get(this.$apiAdress + '/api/order/orderItems?token=' + localStorage.getItem("api_token"), {
                    params: {
                        page: self.orderPage,
                        uuid: self.order.uuid
                    },
                }).then(({ data }) => {
                    self.order_items = data.order_items
                    // if (data.order_items.data.length) {
                    //     self.orderPage += 1;
                    //     self.order_items.push(...data.data);
                    //     $state.loaded();
                    // } else {
                    //     $state.complete();
                    // }
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
        resetListItem(){
            this.itemPage = 1
            this.itemInfId += 1
            this.items = []
        },
        // noteEnter(event,index){
        //     if (event.keyCode === 13) {
        //         this.saveQuantity(index);
        //     }
        // },
        saveQuantity(index){
            let self = this
            var uuid = self.order_items[index].uuid;
            var quantity = self.order_items[index].quantity;
            var note = self.order_items[index].note;
            axios.post(  this.$apiAdress + '/api/order/saveQuantity?token=' + localStorage.getItem("api_token"),
                {
                    uuid: uuid,
                    quantity: quantity,
                    note: note,
                    order_uuid: self.order.uuid
                }
            )
            .then(function (response) {
                self.order = response.data.order
                if(response.data.reload){
                    self.getOrderItems()
                }
            })
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
                self.getOrderItems()
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
                self.getOrderItems()
            })
        },
        printReceipt(){
            this.$router.push({path: `/print/${this.order.uuid.toString()}/receipt`})
        },
        saveOrder(uuid,stat){
            let self = this
            axios.post(  this.$apiAdress + '/api/order/saveOrder?token=' + localStorage.getItem("api_token"),
                {
                    uuid: uuid,
                    stat: stat
                }
            )
            .then(function (response) {
                self.$router.push({path: `${self.order.uuid.toString()}/edit`})
            })

        }
    },
    components:{
        InfiniteLoading,
    },
    mounted(){
        this.resetListItem();
        this.getOrderDetail();
        this.getCategories();
    }
}
</script>

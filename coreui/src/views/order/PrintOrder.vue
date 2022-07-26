<style lang="scss">
    @import "../../assets/scss/print.scss";
</style>

<template>
<div>
                    <span id="printMe" class="ticket">
                        <div style="width:300px;text-align: center;align-content: center;">
                            <img :src="tenant.logo" style="max-height:50px; max-width:300px; width:auto;text-align: center;align-content: center;" alt="Logo">
                        </div>
                        <h4 style="max-width:300px; margin:0;text-align: center;align-content: center;">{{ tenant.name }}</h4>
                        <p style="max-width:300px; font-size:8px; margin:0; text-align: center;align-content: center;">{{ tenant.phone }} | {{ tenant.email }}</p>
                        <!-- <table>
                            <tr>
                                <td class="title">Order</td>
                                <td class="detail">: {{ order.order_number }}</td>
                            </tr>
                            <tr>
                                <td class="title">Date</td>
                                <td class="detail">: {{ nowTime }}</td>
                            </tr>
                            <tr>
                                <td class="title">Cashier</td>
                                <td class="detail">: {{ currentUser }}</td>

                            </tr>
                            <tr>
                                <td class="title">Customer</td>
                                <td class="detail">: {{ order.customer_name }}</td>
                            </tr>
                        </table> -->
                        <table style="width:300px;">
                            <tr>
                                <td>{{ order.order_number }}</td>
                                <td class="righted" style="text-align: right;align-content: right;">{{ nowTime }}</td>
                            </tr>
                            <tr>
                                <td>Cashier : {{ currentUser }}</td>
                                <td style="text-align: right;align-content: right;">Cust : {{ order.customer_name }}</td>
                            </tr>
                        </table>
                        <br>
                        <table style="width:300px;">
                            <thead>
                                <tr>
                                    <th class="quantity" style="border-bottom:1px solid black;">Q.</th>
                                    <th class="description" style="border-bottom:1px solid black;">Item</th>
                                    <th class="price" style="border-bottom:1px solid black;text-align: right;align-content: right;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, $index) in order_items" :key="$index">
                                    <td class="quantity">{{item.quantity}}</td>
                                    <td class="description">{{item.name}}</td>
                                    <td class="price" style="text-align: right;align-content: right;">{{item.price_quantity}}</td>
                                </tr>
                                <tr v-if="order.discount > 0">
                                    <th class="quantity" style="border-top:1px solid black;"></th>
                                    <th class="righted" style="border-top:1px solid black;text-align: right;align-content: right;">Sub</th>
                                    <th class="price-right" style="border-top:1px solid black;text-align: right;align-content: right;">{{order.price_total}}</th>
                                </tr>
                                <tr v-if="order.discount > 0">
                                    <th class="quantity"></th>
                                    <th class="righted" style="text-align: right;align-content: right;">Discount</th>
                                    <th class="price-right" style="text-align: right;align-content: right;">{{order.discount}}</th>
                                </tr>
                                <tr>
                                    <th class="quantity" style="border-top:1px solid black;"></th>
                                    <th class="righted" style="border-top:1px solid black;text-align: right;align-content: right;">Total</th>
                                    <th class="price-right" style="border-top:1px solid black;text-align: right;align-content: right;">{{order.final_price}}</th>
                                </tr>
                            </tbody>
                        </table>
                        <br>
                        <p v-if="tenant.receipt_note" style="width:300px; white-space: pre; margin:0;text-align: center;align-content: center;">{{ tenant.receipt_note }}</p>
                        <p style="width:300px; padding:0;text-align: center;align-content: center;"><span style="font-size:8px;text-align: center;align-content: center;">Powered by <img :src="'/diposicon-dark.png'" style="max-width:12px;"> <b>DIPOS</b></span></p>
                        <!-- <button class="btn btn-secondary novis" @click="goBack()">Back</button>
                        <button class="btn btn-warning novis" @click="printReceipt()">Print</button> -->
                    </span>
                    <button class="btn btn-secondary novis" @click="goBack()">Back</button>
                    <button class="btn btn-warning novis" @click="printReceipt()">Print</button>
                    </div>
</template>

<script>
import axios from 'axios'
// const options = {
//   styles: [
//     'https://dipos.s3.ap-southeast-1.amazonaws.com/css/print-vue.css'
//   ]
// }
export default {
    name: 'PrintOrder',
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
                price_total: '',
                discount: '',
                discount_type: '',
                promotion_id: '',
                final_price: '',
                payment_type: ''
            },
            tenant: {},
            order_items: [],
            currentUser: '',
            nowTime: '',
        }
    },
    methods: {
        goBack() {
            this.$router.go(-1)
            // this.$router.push({name: 'Edit Order', params: this.order.uuid.toString()})
        },
        getOrderDetail (){
            let self = this;
            axios.post(  this.$apiAdress + '/api/order/printOrder?token=' + localStorage.getItem("api_token"),
            {
                uuid: self.$route.params.uuid
            })
            .then(function (response) {
                self.order= response.data.order;
                self.tenant= response.data.tenant;
                self.order_items= response.data.order_items;
                self.currentUser= response.data.user;
            }).catch(function (error) {
                console.log(error);
                self.$router.push({ path: '/login' });
            });
        },
        generateTime(){
            let self = this
            let anyDate = new Date()
            Date.prototype.toShortFormat = function() {
                let monthNames =["Jan","Feb","Mar","Apr",
                                "May","Jun","Jul","Aug",
                                "Sep", "Oct","Nov","Dec"];
                let day = this.getDate();
                let monthIndex = this.getMonth();
                let monthName = monthNames[monthIndex];
                let year = this.getFullYear();
                let hour = this.getHours();
                let minute = this.getMinutes();

                return `${day}-${monthName}-${year} ${hour}:${minute}`;
            }
            self.nowTime = anyDate.toShortFormat()
        },
        printReceipt(){
            const options = {
                name: '_blank',
                specs: [
                    'fullscreen=yes',
                    'titlebar=yes',
                    'scrollbars=yes'
                ],
                styles: [
                    'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css',
                    'https://'+location.hostname+'/print.css',
                ],
                timeout: 1000, // default timeout before the print window appears
                autoClose: true, // if false, the window will not close after printing
                windowTitle: window.document.title, // override the window title
            } 
            this.$htmlToPaper('printMe', options)
        },
    },
    mounted(){
        this.getOrderDetail();
        this.generateTime();
    }
}
</script>

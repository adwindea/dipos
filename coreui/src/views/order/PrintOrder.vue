<style lang="scss">
    @import "../../assets/scss/print.scss";
</style>

<template>
<div>
                    <span id="printMe" class="ticket">
                        <img src="https://dipos.s3.ap-southeast-1.amazonaws.com/image/logo-invoice.jpg" style="max-width:180px;" alt="Logo">
                        <br>
                        <br>
                        <table>
                            <tr>
                                <td class="title">Order ID</td>
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
                        </table><br>
                        <table>
                            <thead>
                                <tr>
                                    <th class="quantity" style="border-bottom:1px solid black;">Q.</th>
                                    <th class="description" style="border-bottom:1px solid black;">Item</th>
                                    <th class="price" style="border-bottom:1px solid black;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, $index) in order_items" :key="$index">
                                    <td class="quantity">{{item.quantity}}</td>
                                    <td class="description">{{item.name}}</td>
                                    <td class="price">{{numberWithCommas(item.quantity*item.price_unformat)}}</td>
                                </tr>
                                <tr v-if="order.discount > 0">
                                    <th class="quantity" style="border-top:1px solid black;"></th>
                                    <th class="righted" style="border-top:1px solid black;">Sub</th>
                                    <th class="price-right" style="border-top:1px solid black;">{{order.price_total}}</th>
                                </tr>
                                <tr v-if="order.discount > 0">
                                    <th class="quantity"></th>
                                    <th class="righted">Discount</th>
                                    <th class="price-right">{{order.discount}}</th>
                                </tr>
                                <tr>
                                    <th class="quantity" style="border-top:1px solid black;"></th>
                                    <th class="righted" style="border-top:1px solid black;">Total</th>
                                    <th class="price-right" style="border-top:1px solid black;">{{order.final_price}}</th>
                                </tr>
                            </tbody>
                        </table>
                        <br>
                        <p class="centered" style="width:180px">Thanks for your purchase!</p>
                        <br>
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
            order_items: [],
            currentUser: '',
            nowTime: '',
        }
    },
    methods: {
        numberWithCommas(x) {
            return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        },
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
            window.print()
            // this.$htmlToPaper('printMe', options)
        },
    },
    mounted(){
        this.getOrderDetail();
        this.generateTime();
    }
}
</script>

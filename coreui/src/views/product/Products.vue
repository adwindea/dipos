<template>
    <CRow>
        <CCol col="12">
            <transition name="slide">
                <CCard>
                    <CCardBody>
                        <h4>
                            Products
                            <CButton color="primary" @click="addProduct()" class="mb-3 float-right"><CIcon name="cilPlus"></CIcon></CButton>
                        </h4>
                        <CDataTable
                            hover
                            :items="items"
                            :fields="fields"
                            :items-per-page="30"
                            pagination
                        >
                            <template #image="{item}">
                                <td>
                                    <a :href="item.img" target="__blank">
                                        <img :src="item.img" style="max-height:60px;max-width:120px;" title="Click for more detail"/>
                                    </a>
                                </td>
                            </template>
                            <template #name="{item}">
                                <td>
                                    {{item.name}}
                                </td>
                            </template>
                            <template #category="{item}">
                                <td>
                                    {{item.category}}
                                </td>
                            </template>
                            <!-- <template #unit="{item}">
                                <td>
                                    {{item.unit}}
                                </td>
                            </template> -->
                            <template #price="{item}">
                                <td>
                                    {{item.price}} IDR
                                </td>
                            </template>
                            <template #action="{item}">
                                <td>
                                    <CButton color="danger" @click="deleteProduct( item.uuid )"><CIcon name="cilTrash"></CIcon></CButton>
                                    <CButton color="warning" @click="editProduct( item.uuid )"><CIcon name="cilPencil"></CIcon></CButton>
                                    <CButton color="success" @click="editIngredient( item.uuid )"><CIcon :content="$options.ingredientIcon"></CIcon></CButton>
                                </td>
                            </template>
                        </CDataTable>
                    </CCardBody>
                </CCard>
            </transition>
        </CCol>
    </CRow>
</template>

<script>
import axios from 'axios'
import { cilSpreadsheet } from '@coreui/icons'
export default {
    ingredientIcon: cilSpreadsheet,
    name: 'Products',
    data () {
        return {
            fields: ['image', 'name', 'category', 'price', 'action'],
            items: [],
            buffor: [],
        }
    },
    methods: {
        getProduct (){
            let self = this;
            axios.get(  this.$apiAdress + '/api/product?token=' + localStorage.getItem("api_token"))
            .then(function (response) {
                self.items = response.data.products;
                self.you = response.data.you;
            }).catch(function (error) {
                console.log(error);
                self.$router.push({ path: '/login' });
            });
        },
        addProduct(){
            this.$router.push({path: 'product/create'});
        },
        deleteProduct(uuid){
            this.$router.push({path: `product/${uuid.toString()}/delete`});
        },
        editProduct(uuid){
            this.$router.push({path: `product/${uuid.toString()}/edit`});
        },
        editIngredient(uuid){
            this.$router.push({path: `product/${uuid.toString()}/ingredient`});
        },
    },
    mounted(){
        this.getProduct();
    }
}
</script>

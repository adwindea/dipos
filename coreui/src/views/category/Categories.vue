<template>
    <CRow>
        <CCol col="12">
            <transition name="slide">
                <CCard>
                    <CCardHeader>
                        <h4>
                            Category
                            <CButton color="primary" @click="addCategory()" class="mb-3 float-right"><CIcon name="cilPlus"></CIcon></CButton>
                        </h4>
                    </CCardHeader>
                    <CCardBody>
                        <CDataTable
                            sorter
                            hover
                            :items="items"
                            :fields="fields"
                            :items-per-page="10"
                            items-per-page-select
                            :tableFilter="{ placeholder: 'Type to search'}"
                            pagination
                        >
                            <template #name="{item}">
                                <td>
                                    {{item.name}}
                                </td>
                            </template>
                            <template #image="{item}">
                                <td class="text-center">
                                    <a :href="item.img" target="_blank">
                                        <img :src="item.img" style="max-height:60px;max-width:120px;" title="Click for more detail"/>
                                    </a>
                                </td>
                            </template>
                            <template #action="{item}">
                                <td class="text-center">
                                    <CButton color="danger" @click="deleteCategory( item.uuid )"><CIcon name="cilTrash"></CIcon></CButton>
                                    <CButton color="warning" @click="editCategory( item.uuid )"><CIcon name="cilPencil"></CIcon></CButton>
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
export default {
    name: 'Categories',
    data () {
        return {
            fields: [
                {key:'name'},
                {key:'image', _classes:'text-center'},
                {key:'action', _classes:'text-center'}
            ],
            items: [],
            buffor: [],
        }
    },
    methods: {
        getCategory (){
            let self = this;
            axios.get(  this.$apiAdress + '/api/category?token=' + localStorage.getItem("api_token"))
            .then(function (response) {
                self.items = response.data.categories;
                self.you = response.data.you;
            }).catch(function (error) {
                console.log(error);
                self.$router.push({ path: '/login' });
            });
        },
        addCategory(){
            this.$router.push({path: 'category/create'});
        },
        deleteCategory(uuid){
            this.$router.push({path: `category/${uuid.toString()}/delete`});
        },
        editCategory(uuid){
            this.$router.push({path: `category/${uuid.toString()}/edit`});
        },
    },
    mounted(){
        this.getCategory();
    }
}
</script>

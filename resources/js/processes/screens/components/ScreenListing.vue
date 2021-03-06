<template>
  <div class="data-table">
    <div class="card card-body table-card">
      <vuetable
        :dataManager="dataManager"
        :sortOrder="sortOrder"
        :css="css"
        :api-mode="false"
        @vuetable:pagination-data="onPaginationData"
        :fields="fields"
        :data="data"
        data-path="data"
        pagination-path="meta"
      >
        <template slot="title" slot-scope="props">
          <b-link
            @click="onAction('edit-screen', props.rowData, props.rowIndex)"
            v-if="permission.includes('edit-screens')"
          >{{props.rowData.title}}</b-link>
          <span v-else="permission.includes('edit-screens')">{{props.rowData.title}}</span>
        </template>

        <template slot="actions" slot-scope="props">
          <div class="actions">
            <div class="popout">
              <b-btn
                variant="link"
                @click="onAction('edit-screen', props.rowData, props.rowIndex)"
                v-b-tooltip.hover
                title="Open Editor"
                v-if="permission.includes('edit-screens')"
              >
                <i class="fas fa-pen-square fa-lg fa-fw"></i>
              </b-btn>
              <b-btn
                variant="link"
                @click="onAction('edit-item', props.rowData, props.rowIndex)"
                v-b-tooltip.hover
                title="Config"
                v-if="permission.includes('edit-screens')"
              >
                <i class="fas fa-cog fa-lg fa-fw"></i>
              </b-btn>
              <b-btn
                variant="link"
                @click="onAction('remove-item', props.rowData, props.rowIndex)"
                v-b-tooltip.hover
                title="Remove"
                v-if="permission.includes('delete-screens')"
              >
                <i class="fas fa-trash-alt fa-lg fa-fw"></i>
              </b-btn>
            </div>
          </div>
        </template>
      </vuetable>
      <pagination
        single="Screen"
        plural="Screens"
        :perPageSelectEnabled="true"
        @changePerPage="changePerPage"
        @vuetable-pagination:change-page="onPageChange"
        ref="pagination"
      ></pagination>
    </div>
  </div>
</template>

<script>
import datatableMixin from "../../../components/common/mixins/datatable";

export default {
  mixins: [datatableMixin],
  props: ["filter", "id", "permission"],
  data() {
    return {
      orderBy: "title",

      sortOrder: [
        {
          field: "title",
          sortField: "title",
          direction: "asc"
        }
      ],

      fields: [
        {
          title: "Name",
          name: "__slot:title",
          field: "title",
          sortField: "title"
        },
        {
          title: "Description",
          name: "description",
          sortField: "description"
        },
        {
          title: "Type",
          name: "type",
          sortField: "type"
        },
        {
          title: "Modified",
          name: "updated_at",
          sortField: "updated_at",
          callback: "formatDate"
        },
        {
          title: "Created",
          name: "created_at",
          sortField: "created_at",
          callback: "formatDate"
        },
        {
          name: "__slot:actions",
          title: ""
        }
      ]
    };
  },

  methods: {
    onAction(actionType, data, index) {
      switch (actionType) {
        case "edit-screen":
          window.location.href =
            "/processes/screen-builder/" + data.id + "/edit";
          break;
        case "edit-item":
          window.location.href = "/processes/screens/" + data.id + "/edit";
          break;
        case "remove-item":
          let that = this;
          ProcessMaker.confirmModal(
            "Caution!",
            "<b>Are you sure to delete the Screen </b>" + data.title + "?",
            "",
            function() {
              ProcessMaker.apiClient
                .delete("screens/" + data.id)
                .then(response => {
                  ProcessMaker.alert("Screen successfully deleted", "success");
                  that.fetch();
                });
            }
          );
          break;
      }
    },
    fetch() {
      this.loading = true;
      //change method sort by slot name
      this.orderBy = this.orderBy === "__slot:title" ? "title" : this.orderBy;
      // Load from our api client
      ProcessMaker.apiClient
        .get(
          "screens" +
            "?page=" +
            this.page +
            "&per_page=" +
            this.perPage +
            "&filter=" +
            this.filter +
            "&order_by=" +
            this.orderBy +
            "&order_direction=" +
            this.orderDirection
        )
        .then(response => {
          this.data = this.transform(response.data);
          this.loading = false;
        });
    }
  },

  computed: {}
};
</script>

<style lang="scss" scoped>
/deep/ th#_total_users {
  width: 150px;
  text-align: center;
}

/deep/ th#_description {
  width: 250px;
}

/deep/ .rounded-user {
  border-radius: 50% !important;
  height: 1.5em;
  margin-right: 0.5em;
}
</style>

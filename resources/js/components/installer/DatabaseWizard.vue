<template>
  <div class="p-3">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <form>
          <div class="card border-0 bg-dark shadow mb-4">
            <div class="card-header app-color text-white border-0 rounded text-center text-capitalize p-4">
              <h4 class="mb-0">
                Install Merchant
              </h4>
            </div>
          </div>
          <div v-if="preloader">
            <preLoader/>
            <h1 class="text-center">Install in progress...</h1>
          </div>
          <div v-else>
            <!--Database credential-->
            <div class="card border-0 shadow mb-4">
              <div class="card-header bg-dark app-color text-white p-4">
                <h5 class="mb-0">
                  <i class="la la-database"/>
                  Database Configuration
                </h5>
              </div>
              <div class="card-body p-5">
                <div class="form-group row align-items-center">
                  <label for="database_connection" class="col-sm-3 mb-sm-0">
                    Database connection
                  </label>
                  <div class="col-sm-9">
                    <select v-model="setupInfo.database_connection" id="database_connection"
                            class="form-control">
                      <option value disabled>{{ 'lang.choose_one' }}</option>
                      <option v-for="connection in connectionList"
                              :value="connection.id">
                        {{ connection.value }}
                      </option>
                    </select>
                    <div class="heightError mb-3" v-if="checkError('database_connection')">
                      <small class="text-danger" v-for="message in errorCollection.database_connection">
                        {{ message }}
                      </small>
                    </div>
                  </div>
                </div>
                <div class="form-group row align-items-center">
                  <label for="database_hostname" class="col-sm-3 mb-sm-0">
                    Database hostname
                  </label>
                  <div class="col-sm-9">
                    <input id="database_hostname"
                           class="form-control"
                           type="text"
                           v-model="setupInfo.database_hostname"
                           placeholder="Enter database hostname"/>
                    <div class="heightError mb-3" v-if="checkError('database_hostname')">
                      <small class="text-danger" v-for="message in errorCollection.database_hostname">
                        {{ message }}
                      </small>
                    </div>
                  </div>

                </div>
                <div class="form-group row align-items-center">
                  <label for="database_port" class="col-sm-3 mb-sm-0">
                    Database port
                  </label>
                  <div class="col-sm-9">
                    <input id="database_port"
                           class="form-control"
                           type="text"
                           v-model="setupInfo.database_port"
                           placeholder="Enter database port"/>
                    <div class="heightError mb-3" v-if="checkError('database_port')">
                      <small class="text-danger" v-for="message in errorCollection.database_port">
                        {{ message }}
                      </small>
                    </div>
                  </div>

                </div>
                <div class="form-group row align-items-center">
                  <label for="database_name" class="col-sm-3 mb-sm-0">
                    Database name
                  </label>
                  <div class="col-sm-9">
                    <input id="database_name"
                           class="form-control"
                           type="text"
                           v-model="setupInfo.database_name"
                           placeholder="Enter database name"/>
                    <div class="heightError mb-3" v-if="checkError('database_name')">
                      <small class="text-danger" v-for="message in errorCollection.database_name">
                        {{ message }}
                      </small>
                    </div>
                  </div>
                </div>
                <div class="form-group row align-items-center">
                  <label for="database_username" class="col-sm-3 mb-sm-0">
                    Database username
                  </label>
                  <div class="col-sm-9">
                    <input id="database_username"
                           class="form-control"
                           type="text"
                           v-model="setupInfo.database_username"
                           placeholder="Enter database username"/>
                    <div class="heightError mb-3" v-if="checkError('database_username')">
                      <small class="text-danger" v-for="message in errorCollection.database_username">
                        {{ message }}
                      </small>
                    </div>
                  </div>
                </div>
                <div class="form-group row align-items-center mb-0">
                  <label for="database_password" class="col-sm-3 mb-sm-0">
                    Database password
                  </label>
                  <div class="col-sm-9">
                    <input id="database_password"
                           class="form-control"
                           type="password"
                           v-model="setupInfo.database_password"
                           placeholder="Enter database password"/>
                    <div class="heightError mb-3" v-if="checkError('database_password')">
                      <small class="text-danger" v-for="message in errorCollection.database_password">
                        {{ message }}
                      </small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Redis credential -->
            <div class="card border-0 shadow mb-4">
              <div class="card-header bg-dark app-color text-white p-4">
                <h5 class="mb-0">
                  <i class="la la-database"/>
                  Redis Configuration
                </h5>
              </div>
              <div class="card-body p-5">
                <div class="form-group row align-items-center">
                  <label for="redis_host" class="col-sm-3 mb-sm-0">
                    Redis host
                  </label>
                  <div class="col-sm-9">
                    <input id="redis_host"
                           class="form-control"
                           type="text"
                           v-model="setupInfo.redis_host"
                           placeholder="Enter redis hostname"/>
                    <div class="heightError mb-3" v-if="checkError('redis_connection')">
                      <small class="text-danger" v-for="message in errorCollection.redis_host">
                        {{ message }}
                      </small>
                    </div>
                  </div>
                </div>
                <div class="form-group row align-items-center">
                  <label for="database_password" class="col-sm-3 mb-sm-0">
                    Redis password
                  </label>
                  <div class="col-sm-9">
                    <input id="database_password"
                           class="form-control"
                           type="password"
                           v-model="setupInfo.redis_password"
                           placeholder="Enter redis password"/>
                    <div class="heightError mb-3" v-if="checkError('redis_password')">
                      <small class="text-danger" v-for="message in errorCollection.redis_password">
                        {{ message }}
                      </small>
                    </div>
                  </div>
                </div>
                <div class="form-group row align-items-center mb-0">
                  <label for="redis_port" class="col-sm-3 mb-sm-0">
                    Redis port
                  </label>
                  <div class="col-sm-9">
                    <input id="redis_port"
                           class="form-control"
                           type="number"
                           v-model="setupInfo.redis_port"
                           placeholder="Enter redis port"/>
                    <div class="heightError mb-3" v-if="checkError('redis_port')">
                      <small class="text-danger" v-for="message in errorCollection.redis_port">
                        {{ message }}
                      </small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Processing -->
            <div class="card border-0 shadow mb-4">
              <div class="card-header bg-dark app-color text-white p-4">
                <h5 class="mb-0">
                  <i class="la la-database"/>
                  Processing Configuration
                </h5>
              </div>
              <div class="card-body p-5">
                <div class="form-group row align-items-center">
                  <label for="redis_host" class="col-sm-3 mb-sm-0">
                    Processing host
                  </label>
                  <div class="col-sm-9">
                    <input id="redis_host"
                           class="form-control"
                           type="text"
                           v-model="setupInfo.processing_host"
                           placeholder="Enter Processing hostname"/>
                    <div class="heightError mb-3" v-if="checkError('processing_host')">
                      <small class="text-danger" v-for="message in errorCollection.redis_host">
                        {{ message }}
                      </small>
                    </div>
                  </div>
                </div>
                <div class="form-group row align-items-center">
                  <label for="redis_host" class="col-sm-3 mb-sm-0">
                    Frontend Domain
                  </label>
                  <div class="col-sm-9">
                    <input id="redis_host"
                           class="form-control"
                           type="text"
                           v-model="setupInfo.app_domain"
                           placeholder="Enter Frontend domain"/>
                    <div class="heightError mb-3" v-if="checkError('processing_host')">
                      <small class="text-danger" v-for="message in errorCollection.redis_host">
                        {{ message }}
                      </small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="">
              <button class="btn-block btn btn-dark btn-lg text-center"
                      type="button"
                      @click="submit">Save and Next
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import {useToast} from "vue-toastification";
import preLoader from "./PreLoader.vue";

const toast = useToast();
export default {
  name: "DatabaseWizard",
  extends: axios,
  components: {
    preLoader
  },
  data() {

    return {
      buttonLoader: false,
      isActiveText: false,
      isDisabled: false,
      preloader: false,
      errorCollection: {},
      connectionList: [
        {id: 'mysql', value: 'mysql'},
      ],
      setupInfo: {
        database_connection: 'mysql',
        database_hostname: 'localhost',
        database_port: 3306,
        redis_host: 'localhost',
        redis_port: 6379,
        processing_host: 'http://localhost:8082',
        app_domain: window.location.hostname.split('.').slice(1).join('.')
      },
    }
  },
  methods: {
    submit() {
      this.errorCollection = {};
      this.buttonLoader = true;
      this.isDisabled = true;
      this.isActiveText = true;
      this.preloader = true;

      const formData = {
        ...this.setupInfo,
      };

      axios.post('/setup/save/', formData)
          .then(response => {
            window.location = "/setup/admin";
          })
          .catch(error => {

            this.buttonLoader = false;
            this.isDisabled = false;
            this.isActiveText = false;
            this.preloader = false;
            if (error.response) {
              error.response.status === 422 ? this.errorCollection = error.response.data.errors : this.errorToaster(error.response.data.errors.join(' '));
            }
          });

    },
    checkError(value) {
      return value in this.errorCollection
    },
    errorToaster(message) {
      toast.error(message);
    },
    successToaster(message) {
      toast.success(message);
    }
  },
}
</script>

<style scoped>

label {
  font-size: 1rem;
}

input::placeholder {
  color: #00000070;
}

</style>


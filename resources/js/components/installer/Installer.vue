<template>
  <div class="p-3">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div>
          <form>
            <div class="card border-0 shadow mb-4">
              <div
                  class="card-header bg-dark position-relative  app-color text-white border-0 rounded text-center text-capitalize p-4">
                <h4 class="mb-0">
                  Install Merchant
                </h4>
              </div>
            </div>
            <div v-if="preloader">
              <preLoader/>
              <h1 class="text-center">Install in progress...</h1>
            </div>
            <!--Admin login-->
            <div class="card border-0 shadow mb-4" v-else>
              <div class="card-header bg-dark app-color text-white p-4">
                <h5 class="mb-0">
                  <i class="la la-user"/>
                  Root User details
                </h5>

              </div>
              <div class="card-body p-4">
                <div class="form-group row align-items-center">
                  <label for="full_name" class="col-sm-3 mb-sm-0">
                    Name
                  </label>
                  <div class="col-sm-9">
                    <input id="full_name"
                           class="form-control"
                           type="text"
                           v-model="setupInfo.name"
                           placeholder="Enter name"/>
                    <div class="heightError mb-2" v-if="checkError('first_name')">
                      <small class="text-danger" v-for="message in errorCollection.name">
                        {{ message }}
                      </small>
                    </div>
                  </div>

                </div>
                <div class="form-group row align-items-center">
                  <label for="email_address" class="col-sm-3 mb-sm-0">
                    Email
                  </label>
                  <div class="col-sm-9">
                    <input id="email_address"
                           class="form-control"
                           name="email"
                           type="email"
                           v-model="setupInfo.email"
                           placeholder="Enter Email"/>
                    <div class="heightError mb-2" v-if="checkError('email')">
                      <small class="text-danger" v-for="message in errorCollection.email">
                        {{ message }}
                      </small>
                    </div>

                  </div>

                </div>
                <div class="form-group row align-items-center">
                  <label for="installer_password" class="col-sm-3 mb-sm-0">
                    Password
                  </label>
                  <div class="col-sm-9">
                    <input id="installer_password"
                           class="form-control"
                           type="password"
                           v-model="setupInfo.password"
                           placeholder="Enter Password"/>
                    <div class="heightError mb-2" v-if="checkError('password')">
                      <small class="text-danger" v-for="message in errorCollection.password">
                        {{ message }}
                      </small>
                    </div>
                  </div>
                </div>
                <div class="form-group row align-items-center mb-0">
                  <label for="installer_password" class="col-sm-3 mb-sm-0">
                    Password confirm
                  </label>
                  <div class="col-sm-9">
                    <input id="installer_password"
                           class="form-control"
                           type="password"
                           v-model="setupInfo.password_confirmation"
                           placeholder="Enter Password confirm"/>
                    <div class="heightError mb-2" v-if="checkError('password_confirmation')">
                      <small class="text-danger" v-for="message in errorCollection.password_confirmation">
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
                      :disabled="isDisabled"
                      @click="submit">Install
              </button>

            </div>

          </form>
        </div>
        <div class="card border-0 shadow mt-4" v-if="finishInstall">
          <div class="card-header bg-dark app-color text-white p-4">
            <h4 class="mb-0">Finish install</h4>
          </div>
          <div class="card-body p-4">
            Installation is complete, to start using the merchant, follow the link
            <a :href="`http://${ finishInstall }`">{{ finishInstall }}</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import preLoader from "./PreLoader.vue";
import {useToast} from "vue-toastification";

const toast = useToast();
export default {
  name: "Layout",
  components: {preLoader},
  extends: axios,
  data() {

    return {
      buttonLoader: false,
      isActiveText: false,
      isDisabled: false,
      preloader: false,
      errorCollection: {},
      finishInstall: null,

      setupInfo: {
        email: '',
        password: '',
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
        ...this.setupInfo
      };


      axios.post('/setup/register', formData)
          .then(response => {
            this.preloader = false;
            this.finishInstall = response.data.app_url
            this.successToaster("Install Success");
          })
          .catch(error => {
            this.buttonLoader = false;
            this.isDisabled = false;
            this.isActiveText = false;
            this.preloader = false;
            this.errorToaster('Something Wrong');
            if (error.response) {
              error.response.status === 422 ? this.errorCollection = error.response.data.errors : this.errorToaster(error.response.data.message);
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

  computed: {
    names() {
      const full_name_spited = this.setupInfo.full_name.split(' ').filter(name => name);

      if (full_name_spited.length) {
        if (full_name_spited.length === 2) {
          return {
            first_name: full_name_spited[0],
            last_name: full_name_spited[1]
          }
        } else if (full_name_spited.length === 1) {
          return {
            first_name: full_name_spited[0],
            last_name: ''
          }
        } else if (full_name_spited.length === 3) {
          return {
            first_name: `${full_name_spited[0]} ${full_name_spited[1]}`,
            last_name: full_name_spited[2]
          }
        } else {
          return {
            first_name: full_name_spited[0],
            last_name: full_name_spited.slice(1, full_name_spited.length).join(' ')
          }
        }
      }
      return {
        first_name: '',
        last_name: ''
      }
    },
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

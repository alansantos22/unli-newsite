import Vuex from 'vuex'

import GeneralModule from './general/General.js'
import ConfigModule from './config/Config.js'

export default new Vuex.Store({
    modules: {
        GeneralModule,
        ConfigModule
    }
})
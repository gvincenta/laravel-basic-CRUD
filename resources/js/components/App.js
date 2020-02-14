import React, { Component } from 'react'
import ReactDOM from 'react-dom'
import { BrowserRouter, Route, Switch } from 'react-router-dom'
import Header from './Header'
import 'bootstrap/dist/css/bootstrap.min.css'
import MainFunctions from './Forms/MainFunctions'
export default class App extends Component {
    render () {
        return (
            <BrowserRouter>

                    <MainFunctions />

            </BrowserRouter>
    )
    }
}

ReactDOM.render(<App />, document.getElementById('app'))

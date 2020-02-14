import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import MainFunctions from './Forms/MainFunctions';
import Navigation from './Navigation';
import {BrowserRouter,Route,Link,Switch} from 'react-router-dom';
import Main from './Books/Main';
import Table from './Authors/Table';
import {Accordion,Card,Button} from 'react-bootstrap';
export default function App(props) {
     const [action,setAction] = useState('');
    const [status,setStatus] = useState('');



    return (
        <Accordion defaultActiveKey="0">
            <Card>
                <Card.Header>
                    <Accordion.Toggle variant="link" eventKey="0">
                        Books And Authors
                    </Accordion.Toggle>
                </Card.Header>
                <Accordion.Collapse eventKey="0">
                    <Main/>
                </Accordion.Collapse>
            </Card>
                <Card>
                    <Card.Header>
                        <Accordion.Toggle variant="link" eventKey="1">
                            Authors
                        </Accordion.Toggle>
                    </Card.Header>
                        <Accordion.Collapse eventKey="1">
                            <Table/>
                        </Accordion.Collapse>
                    </Card>
        </Accordion>



        );

}


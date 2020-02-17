import React, { useState } from 'react';
import ReactDOM from 'react-dom';
 import Search from './Search/Search';
import {BrowserRouter,Route,Link,Switch} from 'react-router-dom';
import Main from './Books/Main';
import Table from './Authors/Table';
import {Accordion,Card,Button} from 'react-bootstrap';
import Add from './Books/Add';
import Export from './Forms/Export';
export default function App(props) {
     const [action,setAction] = useState('');
    const [status,setStatus] = useState('');


    return (
        <div>
            <Accordion defaultActiveKey="0">
                <Card>
                    <Card.Header>
                        <Accordion.Toggle variant="link" eventKey="0">
                            Books And Authors
                        </Accordion.Toggle>
                    </Card.Header>
                    <Accordion.Collapse eventKey="0">
                        <Search/>
                    </Accordion.Collapse>
                </Card>
                <Card>
                    <Card.Header>
                        <Accordion.Toggle variant="link" eventKey="1">
                                Add / Update...
                        </Accordion.Toggle>
                    </Card.Header>
                    <Accordion.Collapse eventKey="1">
                        <Add/>
                    </Accordion.Collapse>
                    </Card>
                <Card>
                    <Card.Header>
                        <Accordion.Toggle variant="link" eventKey="2">
                            Export To...
                        </Accordion.Toggle>
                    </Card.Header>
                    <Accordion.Collapse eventKey="2">
                        <Export/>
                    </Accordion.Collapse>
                </Card>

            </Accordion>
        </div>



        );

}


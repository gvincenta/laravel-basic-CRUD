import React, { useState } from 'react';
import {InputGroup,Navbar,Form,FormControl,Button, Dropdown, Row,Col} from 'react-bootstrap';
import {Link} from 'react-router-dom';
import Axios from 'axios';
import { CsvToHtmlTable } from 'react-csv-to-table';

/** for searching a book by its title / author: */

export default function (props){
    const [title,setTitle] = useState('');
    const [firstName, setFirstName] = useState('');
    const [lastName, setLastName] = useState('');
    const [data,setData] = useState(null);

    if (data){
        return <p> {data.map(v =>{return</li> (v.ID + v.firstName + v.lastName + v.books_ID + v.title ) </li>} )}  </p>;
    }

    return(



        <div >
        <br/>
        <Form   >
            <Row>
            <Col sm="10">
                <Form.Control
                type="text"
                placeholder="Title"
                required
                onChange = {e => setTitle(e.target.value)}
                required
                />
            </Col>
            <Button variant="primary" type="submit"> Search by title</Button>
            </Row>
        </Form>
        <br/>
    <Form onSubmit={(e)=>{
    e.preventDefault();

    Axios
        .get("/api/authors/with-filter",
            {
                params:{
                    firstName,lastName
                }
            })
        .then((res)=>{
            console.log(res.data);
            setData(res.data);
        })
    }
}>
    <Row>


        <Col sm="5">
            <Form.Control
            type="text"
            placeholder="First Name"
            required
            onChange = {e => setFirstName(e.target.value)}
            required
            />
        </Col>
        <Col sm="5">
            <Form.Control
            type="text"
            placeholder="Last Name"
            required
            onChange = {e => setLastName(e.target.value)}
            />
        </Col>
        <Button variant="primary" type="submit"> Search by author </Button>
        </Row>
    </Form>



         </div>);
}

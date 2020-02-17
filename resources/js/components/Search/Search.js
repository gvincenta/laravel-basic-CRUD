import React, { useState } from 'react';
 import {Link} from 'react-router-dom';
import Axios from 'axios';
import { CsvToHtmlTable } from 'react-csv-to-table';
import Spinner from '../Spinner';
import Main from '../Books/Main';
import {Button,Row,Col,ButtonGroup, Form,CardGroup,Card,ListGroup,ListGroupItem} from 'react-bootstrap';

/** for searching a book by its title / author: */

export default function (props){
    const [title,setTitle] = useState('');
    const [firstName, setFirstName] = useState('');
    const [lastName, setLastName] = useState('');
     const [data,setData] = useState(null);
     const [searchBy, setSearchBy] = useState('');
    const [status,setStatus] = useState('');
    const getData = (e)=>{
        if (e){
            e.preventDefault();
        }
        setStatus("loading");
        Axios
            .get("/api/authors/with-filter",
                {
                    params:{
                        firstName,lastName,title
                    }
                })
            .then((res)=>{
                console.log(res.data);
                setData(res.data);

                setStatus("done");
            })
    }
    const columns = [
        {Header: 'authorID',
            accessor: 'ID'},
        {Header: 'firstName',
            accessor: 'firstName'},
        {Header: 'bookID',
         accessor: 'books_ID'},
        {Header: 'Title',
            accessor: 'title'}]
    if (status === "loading"){
        return <Spinner/>;
    }
    if (data  ){
        return (
            <Main data={data} status="done" onReload={getDataByTitle}/>);
    }



    return(
        <div >
        <br/>
        <Form onSubmit={(e)=>{
        getData(e);
        setSearchBy("title");
    }
}>
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
        getData(e);
        setSearchBy("author");
    }}>
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

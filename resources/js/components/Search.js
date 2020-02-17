import React, { useState } from 'react';
 import {Link} from 'react-router-dom';
import Axios from 'axios';
import { CsvToHtmlTable } from 'react-csv-to-table';
import Spinner from './Spinner';
import Table from '../Books/Table';
import {Button,Row,Col,ButtonGroup, Form,CardGroup,Card,ListGroup,ListGroupItem} from 'react-bootstrap';

 /** for searching a book by its title / author: */

export default function (props){
    const [title,setTitle] = useState('');
    const [firstName, setFirstName] = useState('');
    const [lastName, setLastName] = useState('');
     const [data,setData] = useState(null);
     const [by, setBy] = useState(null);
    const [status,setStatus] = useState('');
    const getData = (e,searchBy)=>{
        if (e){
            e.preventDefault();
        }
        setStatus("loading");
        var request;
        //construct request body:
        console.log("run", searchBy);
        if (searchBy === "title"){
            console.log("runnn")
            Axios
                .get("/api/authors/with-filter",
                    {
                        params :{
                            title

                        }
                    })
                .then((res)=>{
                    console.log(res.data);
                    setData(res.data);
                    setBy(searchBy);
                     setStatus("done");
                })
        }
        else if (searchBy === "author"){
            console.log("else runnn")

            Axios
                .get("/api/authors/with-filter",
                    {
                        params :{
                            firstName,lastName

                        }
                    })
                .then((res)=>{
                    console.log(res.data);
                    setData(res.data);
                    setBy(searchBy);

                    setStatus("done");
                })
        }

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



    return(
        <div >

        <br/>

        <Form onSubmit={(e)=>{

        getData(e,"title");

    }
}>
<Row>
    <Col sm="10">
        <Form.Control
    type="text"
    placeholder="Title (not case sensitive)"
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

        getData(e,"author");

    }}>
<Row>


    <Col sm="5">
        <Form.Control
    type="text"
    placeholder="First Name (not case sensitive)"
    required
    onChange = {e => setFirstName(e.target.value)}
    required
    />
    </Col>
    <Col sm="5">
        <Form.Control
    type="text"
    placeholder="Last Name (not case sensitive)"
    required
    onChange = {e => setLastName(e.target.value)}
    />
    </Col>
    <Button variant="primary" type="submit"> Search by author </Button>
    </Row>
    </Form>
     <br/>


     {data ?
     <div>
         <h2> Search results for {title} {firstName} {lastName}</h2>
     <Table data={data} status="done" /></div> :<Table/> }
    </div>
        );
}

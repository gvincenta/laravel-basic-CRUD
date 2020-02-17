import React, { useState } from 'react';
import {Button,Row,Col,ButtonGroup, Form,CardGroup,Card,ListGroup,ListGroupItem} from 'react-bootstrap';

export default function (props) {
    const {getData,setSearchBy,setTitle,setFirstName,setLastName} = props;
    return (
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


    </div>
    );
}

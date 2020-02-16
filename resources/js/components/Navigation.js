import React, { useState } from 'react';
import {Nav,Navbar,Form,FormControl,Button, Dropdown, DropdownButton} from 'react-bootstrap';
import {Link} from 'react-router-dom';
export default function NavbarExample(props){
    return(

        <Navbar bg="dark" variant="dark">
        <Navbar.Brand href="/">Laravel</Navbar.Brand>


        <Form inline>

    <FormControl type="text" placeholder="Search" className="mr-sm-1" align="center" />
        <DropdownButton variant="outline-info" title="By:" id="bg-nested-dropdown">
            <Dropdown.Item eventKey="1">Titles</Dropdown.Item>
            <Dropdown.Item eventKey="2">Authors</Dropdown.Item>
        </DropdownButton>

        </Form>
         </Navbar>

    );
}

import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom';
import Search from './Search';
import { BrowserRouter, Route, Link, Switch } from 'react-router-dom';
import { Accordion, Card, Button } from 'react-bootstrap';
import Add from './Books/Add';
 import Export from './Export';
import introJs from "intro.js";
import "intro.js/introjs.css";
 /**
 * Shows the main page with 3 tabs:
 * 1. Books and Authors table,
 * 2. Adding a book,
 * 3. Export to.
 * @returns a main page with 3 vertical tabs (using accordion).
 */
export default function() {


    return (
        <div>
        <Button onClick={() => introJs().start()} data-step="1" data-intro="Welcome to the app" >Guide Me </Button>
            <Accordion defaultActiveKey="0">
                <Card>
                    <Card.Header>
                        <Accordion.Toggle variant="link" eventKey="0"
                                data-step="2"
                                data-intro= "Click me to show all the books and authors available in the database. If a row has no book, It means that the author of that row is currently  not assigned to any books">
                            Books And Authors
                        </Accordion.Toggle>
                    </Card.Header>
                    <Accordion.Collapse eventKey="0">
                        <Search />
                    </Accordion.Collapse>
                </Card>
                <Card>
                    <Card.Header>
                        <Accordion.Toggle variant="link" eventKey="1"
                         data-step="8"
                         data-intro= "Click here for adding a new book as well as assigning authors to it." >
                            Add a new book
                        </Accordion.Toggle>
                    </Card.Header>
                    <Accordion.Collapse eventKey="1">
                        <Add />
                    </Accordion.Collapse>
                </Card>
                <Card>
                    <Card.Header>
                        <Accordion.Toggle variant="link" eventKey="2">
                            Export
                        </Accordion.Toggle>
                    </Card.Header>
                    <Accordion.Collapse eventKey="2">
                        <Export />
                    </Accordion.Collapse>
                </Card>
            </Accordion>
        </div>
    );
}

import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import Axios from 'axios';
import { CsvToHtmlTable } from 'react-csv-to-table';
import Spinner from './Spinner';
import Table from './Books/Table';
import { Button, Row, Col, Form } from 'react-bootstrap';

/** for searching a book by its title / author: */

export default function(props) {
    //for a search by book's title:
    const [title, setTitle] = useState('');
    //for a search by book's author:
    const [firstName, setFirstName] = useState('');
    const [lastName, setLastName] = useState('');
    //for storing search results from backend:
    const [data, setData] = useState(null);
    //specify by title / author:
    const [by, setBy] = useState(null);
    //fetching status:
    const [status, setStatus] = useState('');
    //fetching data from backend:
    const getData = (e, searchBy) => {
        if (e) {
            e.preventDefault();
        }
        setStatus('loading');
        var request;
        //construct request body:
        console.log('run', searchBy);
        if (searchBy === 'title') {
            console.log('runnn');
            Axios.get('/api/authors/with-filter', {
                params: {
                    title
                }
            }).then(res => {
                console.log(res.data);
                setData(res.data);
                setBy(searchBy);
                setStatus('done');
            });
        } else if (searchBy === 'author') {
            console.log('else runnn');

            Axios.get('/api/authors/with-filter', {
                params: {
                    firstName,
                    lastName
                }
            }).then(res => {
                console.log(res.data);
                setData(res.data);
                setBy(searchBy);

                setStatus('done');
            });
        }
    };
    //shows loading UI:
    if (status === 'loading') {
        return <Spinner />;
    }

    return (
        <div>
            <br />

            <Form
                onSubmit={e => {
                    getData(e, 'title');
                }}
            >
                <Row>
                    <Col sm="10">
                        <Form.Control
                            type="text"
                            placeholder="Title (not case sensitive)"
                            required
                            onChange={e => setTitle(e.target.value)}
                            data-step="3"
                            data-intro="Enter something here for searching a book by its title"
                        />
                    </Col>
                    <Button variant="primary" type="submit" >
                        {' '}
                        Search by title
                    </Button>
                </Row>
            </Form>
            <br />
            <Form
                onSubmit={e => {
                    getData(e, 'author');
                }}
            >
                <Row>
                    <Col sm="5">
                        <Form.Control
                            type="text"
                            placeholder="First Name (not case sensitive)"
                            data-step="4"
                            data-intro="Or, enter here for searching a book by its author"
                            onChange={e => setFirstName(e.target.value)}

                        />
                    </Col>
                    <Col sm="5">
                        <Form.Control
                            type="text"
                            placeholder="Last Name (not case sensitive)"
                            required
                            onChange={e => setLastName(e.target.value)}
                        />
                    </Col>
                    <Button variant="primary" type="submit">
                        {' '}
                        Search by author{' '}
                    </Button>
                </Row>
            </Form>
            <br />

            {data ? ( //displays search result after fetching data from backend:
                <div>
                    <h2>
                        {' '}
                        Search results for {title} {firstName} {lastName}
                    </h2>
                    <Table data={data} status="done" data-step="5"
                        data-intro="Your search result will appear here"/>
                </div>
            ) : (
                <Table  data-step="5"
    data-intro="Without any searches, this table displays all books and authors available in the database."/>
            ) //otherwise, display Books and Authors Table
            }
        </div>
    );
}

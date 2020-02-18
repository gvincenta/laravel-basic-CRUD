import React, { useState, useEffect ,useLayoutEffect } from 'react';
import Axios from 'axios';
import {Button,Row,Col,ButtonGroup, Form,CardGroup,Card,ListGroup,ListGroupItem} from 'react-bootstrap';
import XMLViewer from 'react-xml-viewer';
import Spinner from './Spinner';
import FileDownload from 'js-file-download';
import { CSVLink, CSVDownload } from "react-csv";
import { CsvToHtmlTable } from 'react-csv-to-table';

/** Handles exporting data to XML and CSV */
export default function () {
    //export to either XML or CSV:
    const [type,setType] = useState('');
    //books / authors / authors and books content:
    const [content,setContent] = useState('');
    //UI status:
    const [status,setStatus] = useState('');
    //data from backend:
    const [data, setData] = useState('');
    //url for fetching data from backend:
    const [url,setURL] = useState('');

    //displays loading spinner:
    if (status === "loading"){
        return <Spinner/>;
    }
    //displays the xml with download option:
    if (status ==="done" && type === "xml"){
        return (
            <div>
                <h2> Displaying {content} XML </h2>
                <XMLViewer xml={data}/>
                <a href="" onClick={(e)=>setStatus("download")}> Download me (in compact format) </a>
            </div>
        );
    }
    //displays the csv file with download option:
    if (status ==="done" && type === "csv"){
        return (
            <div>
            <h2> Displaying {content} CSV as a plain text : </h2>

        <CsvToHtmlTable
        data={data}
        csvDelimiter="," />

            <CSVLink data={data} >
            Download me
        </CSVLink>
            </div>
        ) ;
    }
    //downloads the xml file:
    if (status==="download" && type === "xml"){
        return FileDownload(data,content+".xml");
    }


    //displays the form to choose what content and type is to be exported:
    return (
        <Form onSubmit =
        { (e) =>{
            e.preventDefault();
            //sets URL properly:
            var url = "/api/" + content + "/export/" + type.toUpperCase()
            if (content === "authors and books"){
                 url = "/api/authors/export/" + type.toUpperCase() +"/with-books"
            }
            console.log("URL", url);
            setURL(url);

            //for displaying spinner:
            setStatus("loading");
            //fetching data from database:
            Axios.get(url).then(
                (res)=>{
                    //data is ready:
                    setData(res.data);
                    setStatus("done");
                }
            );
        }}>


            <fieldset>
                <Form.Group as={Row}>
                    <Form.Label as="type" column sm={2}>
                        Export to:
                    </Form.Label>
                    <Col>
                        <Form.Check
                        type="radio"
                        label="XML"
                        name="type"
                        id="xml"
                        onClick={e=>setType(e.target.id)}
                        required
                        />
                        <Form.Check
                        type="radio"
                        label="CSV"
                        name="type"
                        id="csv"
                        onClick={e=>setType(e.target.id)}
                        />
                    </Col>
                </Form.Group>
            </fieldset>
            <fieldset>
                <Form.Group as={Row}>
                    <Form.Label as="content" column sm={2}>
                        Radios
                    </Form.Label>
                    <Col>
                        <Form.Check
                        type="radio"
                        label="books only"
                        name="content"
                        id="books"
                        onClick={e=>setContent(e.target.id)}
                        />
                        <Form.Check
                        type="radio"
                        label="authors only"
                        name="content"
                        id="authors"
                        onClick={e=>setContent(e.target.id)}
                        />
                        <Form.Check
                        type="radio"
                        label="authors and books"
                        name="content"
                        id="authors and books"
                        onClick={e=>setContent(e.target.id)}
                        required
                        />
                    </Col>
                </Form.Group>
            </fieldset>
            <Button variant="primary" type="submit"> submit </Button>
        </Form>
    );

}

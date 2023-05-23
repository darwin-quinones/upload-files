import React, { useState } from "react";

export default function Dashboard() {
    const [files, setFiles] = useState('');
    //state for checking file size
    const [fileSize, setFileSize] = useState(true);
    const [filesNumber, setFilesNumber] = useState(true)
    // for file upload progress message
    const [fileUploadProgress, setFileUploadProgress] = useState(false);
    //for displaying response message
    const [fileUploadResponse, setFileUploadResponse] = useState(null);
    //base end point url
    const FILE_UPLOAD_BASE_ENDPOINT = "http://localhost:8000";

    const uploadFileHandler = (event) => {
        setFiles(event.target.files);
    };

    const fileSubmitHandler = (event) => {
        event.preventDefault();
        setFileSize(true);
        setFileUploadProgress(true);
        setFileUploadResponse(null);

        const formData = new FormData();
        var totalFilesSize = 0;
        let allowedFilesSize = 50 // 10 MB
        let allowedFilesNumber = 20
        for (let i = 0; i < files.length; i++) {
            var filesize = files[i].size / 1024;
            filesize = (Math.round((filesize / 1024) * 100) / 100);
            totalFilesSize = totalFilesSize + filesize;
            //formData.append('files', files[i])
            formData.append(files[i].name, files[i])
        }
        console.log(totalFilesSize)
        if (totalFilesSize > allowedFilesSize) {
            setFileSize(false);
            setFileUploadProgress(false);
            setFileUploadResponse(null);
            return;
        } if (files.length > allowedFilesNumber) {
            setFilesNumber(false)
            setFileUploadProgress(false);
            setFileUploadResponse(null);
        }

        const requestOptions = {
            method: 'POST',
            body: formData
        };
        fetch(FILE_UPLOAD_BASE_ENDPOINT + '/file-upload-liquidaciones', requestOptions)
            .then(async response => {
                const isJson = response.headers.get('content-type')?.includes('application/json');
                const data = isJson && await response.json();

                // check for error response
                if (!response.ok) {
                    // get error message
                    const error = (data && data.message) || response.status;
                    setFileUploadResponse(data.message);
                    return Promise.reject(error);
                }

                console.log(data.message);
                setFileUploadResponse(data.message);
            })
            .catch(error => {
                console.error('Error while uploading file!', error);
            });
        setFileUploadProgress(false);
    };

    return (

        <form onSubmit={fileSubmitHandler}>
            <h1>Cargar liquidaciones</h1> <br/>
            <input type="file" multiple onChange={uploadFileHandler} />
            <button type='submit'>Upload</button>
            {!fileSize && <p style={{ color: 'red' }}>File size exceeded!!</p>}
            {!filesNumber && <p style={{ color: 'red' }}>Files Number exceeded!!</p>}
            {fileUploadProgress && <p style={{ color: 'red' }}>Uploading File(s)</p>}
            {fileUploadResponse != null && <p style={{ color: 'green' }}>{fileUploadResponse}</p>}
        </form>

    );
}

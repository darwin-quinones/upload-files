
import React, { useState } from 'react';
import '../../../node_modules/bootstrap/dist/css/bootstrap.css'

export default function ProgressBarExample1() {
    //base end point url
    const URL_PROCESS = 'http://localhost:8000/process-file-uploaded'
    const [files, setFiles] = useState('');
    //state for checking file size
    const [fileSize, setFileSize] = useState(true);
    const [filesNumber, setFilesNumber] = useState(true)
    let [totalFilesSize, setTotalFilesSize] = useState(0)
    let allowedFilesSize = 50
    let [calculateTotalFileSize, setCalculateTotalFileSize] = useState(0.1)
    //base end point url
    const FILE_UPLOAD_BASE_ENDPOINT = "http://localhost:8000";

    const [progress, setProgress] = useState(0);

    const uploadFileHandler = (event) => {
        setFiles(event.target.files);
    };

    const getEventSource = (id_tabla_ruta, totalFileSize) => {
        const URL_PROCESS_FILES_WITH_DATA = URL_PROCESS + `?id_tabla_ruta=${id_tabla_ruta}&total_files_size=${totalFileSize}`
        var evtSource = new EventSource(URL_PROCESS_FILES_WITH_DATA);


        evtSource.addEventListener("message", function (event) {
            const obj = JSON.parse(event.data);
            console.log('obj: ', obj)
            var fileProgress = parseInt(obj.progress)
            setProgress(fileProgress)
            if (isNaN(fileProgress)) {
                setProgress(100);
            }
            if(obj.resultado){
                console.log('obj.resultado: '+ obj.resultado)
                console.log('Ha terminado el proceso')
                setProgress(100);
                alert('Los archivos se han cargado exitosamente')
                setProgress(0)
            }
        }, false);


        evtSource.addEventListener('error', (event) => {
            //console.log(event);
            evtSource.close();
        });

        return () => {
            evtSource.close();
        };
    }

    const fileSubmitHandler = (event) => {
        event.preventDefault();
        setFileSize(true);

        const formData = new FormData();

        //let allowedFilesSize = 50 // 10 MB
        let allowedFilesNumber = 20
        for (let i = 0; i < files.length; i++) {
            var filesize = files[i].size;
            setCalculateTotalFileSize(calculateTotalFileSize += filesize);
            formData.append(files[i].name, files[i])

        }
        setTotalFilesSize(20)

        if (totalFilesSize > allowedFilesSize) {
            setFileSize(false);
            return;
        } if (files.length > allowedFilesNumber) {
            setFilesNumber(false)
        }
        const requestOptions = {
            method: 'POST',
            body: formData
        };
        fetch(FILE_UPLOAD_BASE_ENDPOINT + '/upload-files', requestOptions)
            .then(async response => {
                const data = await response.json();
                console.log(data);
                console.log(response)
                // check for error response
                if (data.success === true) {
                    getEventSource(data.id_tabla_ruta, calculateTotalFileSize)
                }
                else if(data.warning){
                    alert('archivos ya existen')
                }
                else if(data.error === true) {
                    console.log('something went wrong')
                }
            })
            .catch(error => {
                canCalculateTime = false
                setProgress(0)
                console.error('Error while uploading file!', error);
            })
    }
    return (

        <form className="row g-3" onSubmit={fileSubmitHandler}>
            <input type="file" className="form-control" multiple onChange={uploadFileHandler} />
            <button type='submit' className="btn btn-primary">Upload</button>
            {!fileSize && <p style={{ color: 'red' }}>File size exceeded!!</p>}
            {!filesNumber && <p style={{ color: 'red' }}>Files Number exceeded!!</p>}
            <div className="progress" style={{ height: 30 }}>
                <div className="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow={`${progress}%`} aria-valuemin="0" aria-valuemax="100" style={{ width: `${progress}%` }}>{progress}</div>
            </div>
        </form>

    );
};


